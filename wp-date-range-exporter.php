<?php
/**
 * Plugin Name: WP Date Range Post Exporter
 * Description: カレンダーで指定した期間、または四半期などの指定範囲の記事一覧をCSVやテキスト形式でエクスポートするプラグイン。
 * Version: 1.1.2
 * Author: lumenHero
 */

// 直接アクセスを防止
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 1. メニューの追加
add_action( 'admin_menu', 'wdre_add_admin_menu' );
function wdre_add_admin_menu() {
    add_management_page(
        '記事エクスポート',
        '記事エクスポート',
        'manage_options',
        'wp-date-range-exporter',
        'wdre_render_admin_page'
    );
}

// 2. 管理画面の描画
function wdre_render_admin_page() {
    ?>
    <div class="wrap">
        <h1>記事一覧エクスポート</h1>
        <p>開始日と終了日を指定して、該当期間に公開された記事の一覧を出力します。</p>
        
        <div style="margin: 20px 0; padding: 15px; background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
            <strong style="display:inline-block; margin-right: 10px;">期間をクイック選択: </strong>
            
            <!-- 追加: 年を選択するドロップダウン -->
            <select id="target_year" style="margin-right: 10px;">
                <?php
                global $wpdb;
                // 一番古い公開済み記事の年を取得
                $oldest_post =$wpdb->get_var("SELECT post_date FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post' ORDER BY post_date ASC LIMIT 1");
                $oldest_year =$oldest_post ? (int) date('Y', strtotime($oldest_post)) : (int) date('Y');$current_year = (int) date('Y');
                
                // 現在の年から一番古い記事の年までループしてoptionを生成
                for ( $i =$current_year; $i >=$oldest_year; $i-- ) {$selected = ( $i ===$current_year ) ? 'selected' : '';
                    echo '<option value="' . esc_attr($i) . '" ' . $selected . '>' . esc_html($i) . '年</option>';
                }
                ?>
            </select>

            <button type="button" class="button" onclick="wdreSetQuarter(1)">第1四半期 (1-3月)</button>
            <button type="button" class="button" onclick="wdreSetQuarter(2)">第2四半期 (4-6月)</button>
            <button type="button" class="button" onclick="wdreSetQuarter(3)">第3四半期 (7-9月)</button>
            <button type="button" class="button" onclick="wdreSetQuarter(4)">第4四半期 (10-12月)</button>
            <button type="button" class="button" onclick="wdreSetYear()">1年間</button>
        </div>

        <form method="post" action="">
            <?php wp_nonce_field( 'export_range_posts', 'range_export_nonce' ); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="start_date">開始日</label></th>
                    <td><input type="date" name="start_date" id="start_date" required></td>
                </tr>
                <tr>
                    <th scope="row"><label for="end_date">終了日</label></th>
                    <td><input type="date" name="end_date" id="end_date" required></td>
                </tr>
                <tr>
                    <th scope="row"><label for="format">出力形式</label></th>
                    <td>
                        <select name="format" id="format">
                            <option value="csv">CSV形式</option>
                            <option value="md_list">Markdown形式 (リスト)</option>
                            <option value="md_table">Markdown形式 (テーブル)</option>
                            <option value="txt">プレーンテキスト形式</option>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button( 'エクスポートを実行' ); ?>
        </form>
    </div>

    <script>
    function wdreFormatDate(date) {
        var y = date.getFullYear();
        var m = ('0' + (date.getMonth() + 1)).slice(-2);
        var d = ('0' + date.getDate()).slice(-2);
        return y + '-' + m + '-' + d;
    }

    function wdreSetDateRange(startMonth, startDay, endMonth, endDay) {
        // セレクトボックスから選択された年を取得
        var targetYear = document.getElementById('target_year').value;
        var year = targetYear ? parseInt(targetYear, 10) : new Date().getFullYear();
        
        var start = new Date(year, startMonth - 1, startDay);
        var end = new Date(year, endMonth, 0); 
        
        document.getElementById('start_date').value = wdreFormatDate(start);
        document.getElementById('end_date').value = wdreFormatDate(end);
    }

    function wdreSetQuarter(q) {
        if (q === 1) wdreSetDateRange(1, 1, 3, 31);
        if (q === 2) wdreSetDateRange(4, 1, 6, 30);
        if (q === 3) wdreSetDateRange(7, 1, 9, 30);
        if (q === 4) wdreSetDateRange(10, 1, 12, 31);
    }

    function wdreSetYear() {
        wdreSetDateRange(1, 1, 12, 31);
    }
    </script>
    <?php
}

// 3. エクスポート処理（ファイルダウンロード）
add_action( 'admin_init', 'wdre_process_export_request' );
function wdre_process_export_request() {
    // ノンスの確認
    if ( ! isset( $_POST['range_export_nonce'] ) || ! wp_verify_nonce( $_POST['range_export_nonce'], 'export_range_posts' ) ) {
        return;
    }

    // 権限チェック
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'この操作を行う権限がありません。' );
    }

    $start_date = sanitize_text_field($_POST['start_date'] );
    $end_date   = sanitize_text_field($_POST['end_date'] );
    $format     = sanitize_text_field($_POST['format'] );

    if ( empty( $start_date ) || empty($end_date ) ) {
        return;
    }

    $args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'date_query'     => array(
            array(
                'after'     => $start_date,
                'before'    => $end_date,
                'inclusive' => true,
            ),
        ),
        'order'          => 'ASC',    // ← 古い順（昇順）
        'orderby'        => 'date',   // ← 日付を基準
    );

    $query = new WP_Query($args );
    
    if ( $format === 'csv' ) {
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=posts_export_' . $start_date . '_to_' . $end_date . '.csv' );$output = fopen( 'php://output', 'w' );
        // BOM出力
        fwrite( $output, "\xEF\xBB\xBF" );
        
        // PHPの仕様変更対策: 引数を明示的に指定する
        fputcsv( $output, array( '公開日', 'タイトル', 'URL' ), ',', '"', '\\' );
        
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {$query->the_post();
                fputcsv( $output, array( get_the_date( 'Y-m-d' ), get_the_title(), get_permalink() ), ',', '"', '\\' );
            }
        }
        fclose( $output );
        exit;
    } else {
        // md_list と md_table の場合は .md 拡張子、それ以外は .txt
        $ext = ( $format === 'md_list' ||$format === 'md_table' ) ? 'md' : 'txt';
        header( 'Content-Type: text/plain; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=posts_export_' . $start_date . '_to_' . $end_date . '.' .$ext );
        
        // Markdownテーブル形式の場合、最初にテーブルのヘッダーを出力
        if ( $format === 'md_table' ) {
            echo "| 公開日 | 記事タイトル |\r\n";
            echo "| :--- | :--- |\r\n";
        }
        
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();$title = get_the_title();
                $url   = get_permalink();$date  = get_the_date( 'Y-m-d' );
                
                if ( $format === 'md_table' ) {
                    // テーブル形式
                    echo "| {$date} | [{$title}]({$url}) |\r\n";
                } elseif ( $format === 'md_list' ) {
                    // リスト形式（Zennなどで見やすい形式）
                    echo "- `{$date}` [{$title}]({$url})\r\n";
                } else {
                    // txt形式（カンマ区切り）
                    echo $date . ", " . $title . ", " . $url . "\r\n";
                }
            }
        } else {
            echo '指定期間内の記事はありません。';
        }
        exit;
    }
}
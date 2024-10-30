<?php
/*
  Plugin Name: ミエルカヒートマップ タグマネージャー
  Plugin URI: https://wordpress.org/plugins/mieruca-heatmap-tag-manager/
  Description: Mieruca Heatmap のタグを簡単設定！
  Version: 1.0.0
  Author: Mieruca Heatmap
  Author URI: https://mieru-ca.com/heatmap/
  License: Copyright 2016-2021 Faber Company
 */
add_action('init', 'MierucaHeatmapTagManager::init');
add_action('wp_head', 'MierucaHeatmapTagManager::my_custom_js');
class MierucaHeatmapTagManager
{
    const VERSION           = '1.0.0';
    const PLUGIN_ID         = 'mieruca-heatmap-tag-manager';
    const CREDENTIAL_ACTION = self::PLUGIN_ID . '-nonce-action';
    const CREDENTIAL_NAME   = self::PLUGIN_ID . '-nonce-key';
    const PLUGIN_DB_PREFIX  = self::PLUGIN_ID . '_';
    const PLUGIN_DB_SUFFIX  = 'site-code';
    const CONFIG_MENU_SLUG  = self::PLUGIN_ID . '-config';
    const REGEX_MIERUCA_HEATMAP_SITE_CODE = '/\d{9}/';
    const REGEX_MIERUCA_HEATMAP_TAG = '/^--BeginMierucaEmbedCode--scripttypetextjavascriptidmierucajswindow__fidwindow__fid__fidpush\d{9}functionfunctionmierucaiftypeofwindow__fjsldundefinedreturnwindow__fjsld1varfjsdocumentcreateElementscriptfjstypetextjavascriptfjsasynctruefjsidfjssyncvartimestampnewDatefjssrchttpsdocumentlocationprotocolhttpshttphmmieru-cacomservicejsmieruca-hmjsvtimestampgetTimevarxdocumentgetElementsByTagNamescript0xparentNodeinsertBeforefjsxsetTimeoutmieruca500documentreadyStatecompletewindowattachEventwindowattachEventonloadmierucawindowaddEventListenerloadmierucafalsemierucascript--EndMierucaEmbedCode--$/';
    const DEFAULT_SITE_CODE = "";
    const TYPE_SAVE = "save";
    const TYPE_DELETE = "delete";

    static function init()
    {
        return new self();
    }

    function __construct()
    {
        if (is_admin() && is_user_logged_in()) {
            add_action('admin_menu', [$this, 'set_plugin_menu']);
            add_action('admin_menu', [$this, 'set_plugin_sub_menu']);
            add_action('admin_init', [$this, 'save_config']);
        }
    }

    function show_about_plugin()
    { ?>
        <div class="wrap">
            <h1>ミエルカヒートマップ</h1>
            <p>早速タグを設置してみましょう！</p>
            <a href="./admin.php?page=mieruca-heatmap-tag-manager-config">設定画面はこちら</a>
        </div>
    <?php
    }

    static function my_custom_js()
    {
        $siteCode = MierucaHeatmapTagManager::find_site_code();
        if (preg_match(self::REGEX_MIERUCA_HEATMAP_SITE_CODE, $siteCode)) {
            echo MierucaHeatmapTagManager::generate_mieruca_heatmap_tag();
        }
    }

    static function find_site_code()
    {
        return get_option(self::PLUGIN_DB_PREFIX . self::PLUGIN_DB_SUFFIX);
    }

    static function generate_mieruca_heatmap_tag()
    {
        return "<!-- Begin Mieruca Embed Code --><script type=\"text/javascript\" id=\"mierucajs\">window.__fid = window.__fid || [];__fid.push([" . MierucaHeatmapTagManager::find_site_code() . "]);(function() {function mieruca(){if(typeof window.__fjsld != \"undefined\") return; window.__fjsld = 1; var fjs = document.createElement('script'); fjs.type = 'text/javascript'; fjs.async = true; fjs.id = \"fjssync\"; var timestamp = new Date;fjs.src = ('https:' == document.location.protocol ? 'https' : 'http') + '://hm.mieru-ca.com/service/js/mieruca-hm.js?v='+ timestamp.getTime(); var x = document.getElementsByTagName('script')[0]; x.parentNode.insertBefore(fjs, x); };setTimeout(mieruca, 500); document.readyState != \"complete\" ? (window.attachEvent ? window.attachEvent(\"onload\", mieruca) : window.addEventListener(\"load\", mieruca, false)) : mieruca();})();</script><!-- End Mieruca Embed Code -->";
    }

    function save_site_code(string $siteCode)
    {
        return update_option(self::PLUGIN_DB_PREFIX . self::PLUGIN_DB_SUFFIX, $siteCode);
    }

    function set_plugin_menu()
    {
        add_menu_page(
            'ミエルカヒートマップ',
            'ミエルカヒートマップ',
            'manage_options',
            'mieruca-heatmap-tag-manager',
            [$this, 'show_about_plugin'],
            'dashicons-format-gallery',
            99
        );
    }

    function set_plugin_sub_menu()
    {
        add_submenu_page(
            'mieruca-heatmap-tag-manager',
            '設定',
            '設定',
            'manage_options',
            'mieruca-heatmap-tag-manager-config',
            [$this, 'show_config_form']
        );
    }

    function show_config_form()
    {
        $title = get_option(self::PLUGIN_DB_PREFIX . "_title"); ?>
        <div class="wrap">
            <table style="table-layout: fixed; width: 100%;">
                <tr>
                    <td style="width: 30%;">
                        <h1>ミエルカヒートマップ</h1>
                        <p>タグを設定しよう！</p>
                        <?php wp_nonce_field(self::CREDENTIAL_ACTION, self::CREDENTIAL_NAME) ?>

                        <p>
                            <label for="tag">タグ入力フォーム</label>
                            <textarea name="tag" style="width: 100%;" form="configure"></textarea>
                        </p>
                        <table>
                            <tr>
                                <td>
                                    <form method="post" id="configure">
                                        <?php wp_nonce_field(self::CREDENTIAL_ACTION, self::CREDENTIAL_NAME) ?>
                                        <input type="hidden" name="type" form="configure" value="<?php echo esc_attr(MierucaHeatmapTagManager::TYPE_SAVE) ?>" />
                                        <input type="submit" value="保存" form="configure" class="button button-primary button-lg" />
                                    </form>
                                </td>
                                <td>
                                    <form method="post" id="delete">
                                        <?php wp_nonce_field(self::CREDENTIAL_ACTION, self::CREDENTIAL_NAME) ?>
                                        <input type="hidden" name="type" form="delete" value="<?php echo esc_attr(MierucaHeatmapTagManager::TYPE_DELETE) ?>" />
                                        <input type="submit" value="削除" form="delete" class="button button-primary button-lg button-red" />
                                    </form>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 40%;">
                        <div style="border-style: solid; border-width: 2px">
                            <p>
                                左の入力フォームに、ミエルカヒートマップで指定されたタグを入力してください。
                                <br /><span style="color: #7c7c7c">※タグの出力方法は<a href="https://fabercompany.zendesk.com/hc/ja/articles/115004869988" target="_blank" rel="noopener noreferrer">【ヒートマップ】初期設定方法</a>をご覧ください</span><br />
                                現在の設定では、以下のタグが埋め込まれます。
                            <p>
                                <?php $siteCode = MierucaHeatmapTagManager::find_site_code();
                                if (!preg_match(self::REGEX_MIERUCA_HEATMAP_SITE_CODE, $siteCode)) {
                                ?><span style="color: #db2222">タグが未設定です。</span>
                                <?php } else { ?>
                                    <span style="color: #3d3d3"><?php echo esc_js(MierucaHeatmapTagManager::generate_mieruca_heatmap_tag()); ?></span>
                                <?php } ?>
                            </p>
                            </p>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
<?php
    }
    function save_config()
    {
        if (isset($_POST[self::CREDENTIAL_NAME]) && $_POST[self::CREDENTIAL_NAME]) {
            if (check_admin_referer(self::CREDENTIAL_ACTION, self::CREDENTIAL_NAME)) {
                if ($_POST['type'] == MierucaHeatmapTagManager::TYPE_SAVE) {
                    $inputValue = sanitize_html_class($_POST['tag']) ? sanitize_html_class($_POST['tag']) : "";
                    if (preg_match(self::REGEX_MIERUCA_HEATMAP_TAG, $inputValue)) {
                        preg_match(self::REGEX_MIERUCA_HEATMAP_SITE_CODE, $inputValue, $siteCode);
                        $this->save_site_code($siteCode[0]);
                        wp_safe_redirect(menu_page_url(self::CONFIG_MENU_SLUG));
                        exit;
                    }
                } else if ($_POST['type'] == MierucaHeatmapTagManager::TYPE_DELETE) {
                    $this->save_site_code(MierucaHeatmapTagManager::DEFAULT_SITE_CODE);
                    wp_safe_redirect(menu_page_url(self::CONFIG_MENU_SLUG));
                    exit;
                }
            }
        }
    }
}

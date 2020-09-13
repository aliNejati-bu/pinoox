<?php
/**
 *      ****  *  *     *  ****  ****  *    *
 *      *  *  *  * *   *  *  *  *  *   *  *
 *      ****  *  *  *  *  *  *  *  *    *
 *      *     *  *   * *  *  *  *  *   *  *
 *      *     *  *    **  ****  ****  *    *
 * @author   Pinoox
 * @link https://www.pinoox.com/
 * @license  https://opensource.org/licenses/MIT MIT License
 */

namespace pinoox\app\com_pinoox_manager\controller\api\v1;


use pinoox\app\com_pinoox_manager\component\Wizard;
use pinoox\component\app\AppProvider;
use pinoox\component\File;
use pinoox\component\Lang;
use pinoox\component\Response;
use pinoox\component\Url;

class TemplateController extends MasterConfiguration
{
    public function get($packageName)
    {
        $folders = File::get_dir_folders(path("~apps>$packageName>theme>"));
        $templates = [];
        foreach ($folders as $folder) {
            $metaJson = $folder . 'meta.json';
            if (!file_exists($metaJson)) continue;

            $meta = json_decode(file_get_contents($metaJson), true);
            $coverDefault = Url::file('resources/theme.jpg');

            $meta['cover'] = (!empty($meta['cover'])) ? Url::check(Url::file('~apps/' . $packageName . '/theme/' . $meta['name'] . '/' . $meta['cover']), $coverDefault) : $meta['cover'];

            if (empty($meta['title'][Lang::current()])) {
                $first = reset($meta['title']);
                $meta['template_name'] = $first;
            } else {
                $meta['template_name'] = $meta['title'][Lang::current()];
            }
            $meta['folder'] = File::name($folder);

            AppProvider::app($packageName);
            $current = AppProvider::get('theme');

            $meta['activate'] = $current === $meta['folder'];
            $templates[] = $meta;
        }

        Response::json($templates, !empty($templates));
    }

    public function install($uid, $packageName)
    {
        if (empty($packageName))
            Response::json(rlang('manager.request_install_template_not_valid'), false);

        if (!Wizard::is_installed($packageName))
            Response::json(rlang('manager.there_is_no_app'), false);

        $file = Wizard::get_downloaded_template($uid);
        $meta = Wizard::pullTemplateMeta($file);

        Wizard::installTemplate($file, $packageName, $meta);
        Response::json(rlang('manager.done_successfully'), true);
    }

    public function set($packageName, $folderName)
    {
        AppProvider::app($packageName);
        AppProvider::set('theme', $folderName);
        AppProvider::save();
    }

    public function remove($packageName, $folderName)
    {
        Wizard::deleteTemplate($packageName, $folderName);
        Response::json(rlang('manager.done_successfully'), true);
    }
}

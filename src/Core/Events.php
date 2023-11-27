<?php
/**
 * This Software is the property of OXID eSales and is protected
 * by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license key
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2021
 */

namespace OxidSolutionCatalysts\EasyCredit\Core;

use OxidEsales\DoctrineMigrationWrapper\MigrationsBuilder;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\DbMetaDataHandler;
use OxidEsales\Eshop\Core\Field;
use \OxidEsales\Eshop\Core\Model\MultiLanguageModel;
use OxidEsales\Eshop\Application\Model\Content;
use OxidEsales\Eshop\Core\Registry;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Class oxpsEasyCreditModule
 * Handles module setup, provides additional tools and module related helpers.
 *
 * @codeCoverageIgnore
 */
class Events
{

    /**
     * Class constructor.
     * Sets current module main data and loads the rest module info.
     *
     * Can see no reason for this
    function __construct()
    {
        $sModuleId = 'oxpseasycredit';

        $this->setModuleData(
            array(
                 'id'          => $sModuleId,
                 'title'       => 'OXPS Easy Credit',
                 'description' => 'OXPS Easy Credit Module',
            )
        );

        $this->load($sModuleId);

        Registry::set('oxpsEasyCreditModule', $this);
    }*/


    /**
     * Module activation script.
     */
    public static function onActivate()
    {
        self::executeModuleMigrations();
        self::_dbEventShopSpecific('install_shopspecific.sql', 'Error activating module: ');
        return self::_dbEvent('install.sql', 'Error activating module: ');
    }

    /**
     * Module deactivation script.
     */
    public static function onDeactivate()
    {
        self::_dbEvent('uninstall.sql', 'Error deactivating module: ');
    }

    /**
     * Clean temp folder content.
     *
     * @param string $sClearFolderPath Sub-folder path to delete from. Should be a full, valid path inside temp folder.
     *
     * @return boolean
     */
    public static function clearTmp($sClearFolderPath = '')
    {
        $sFolderPath = self::_getFolderToClear($sClearFolderPath);
        $hDirHandler = opendir($sFolderPath);

        if (!empty($hDirHandler)) {
            while (false !== ($sFileName = readdir($hDirHandler))) {
                $sFilePath = $sFolderPath . DIRECTORY_SEPARATOR . $sFileName;
                self::_clear($sFileName, $sFilePath);
            }

            closedir($hDirHandler);
        }

        return true;
    }

    /**
     * Get translated string by the translation code.
     *
     * @param string  $sCode
     * @param boolean $blUseModulePrefix If True - adds the module translations prefix, if False - not.
     *
     * @return string
     */
    public function translate($sCode, $blUseModulePrefix = true)
    {
        if ($blUseModulePrefix) {
            $sCode = 'OXPS_EASYCREDIT_' . $sCode;
        }

        return Registry::getLang()->translateString($sCode, Registry::getLang()->getBaseLanguage(), false);
    }

    /**
     * Get module path.
     *
     * @return string Full path to the module directory.
     */
    public function getPath()
    {
        return Registry::getConfig()->getModulesDir() . 'oxps/easycredit-module/';
    }

    /**
     * Install/uninstall event.
     * Executes SQL queries form a file.
     *
     * @param string $sSqlFile      SQL file located in module docs folder (usually install.sql or uninstall.sql).
     * @param string $sFailureError An error message to show on failure.
     */
    protected static function _dbEvent($sSqlFile, $sFailureError = 'Operation failed: ')
    {
        try {
            $oDb  = DatabaseProvider::getDb();
            $sSql = file_get_contents(dirname(__FILE__) . '/../../installments/' . (string) $sSqlFile);
            $aSql = (array) explode(';', $sSql);

            foreach ($aSql as $sQuery) {
                if (!empty($sQuery)) {
                    $oDb->execute($sQuery);
                }
            }
        } catch (\Exception $ex) {
            error_log($sFailureError . $ex->getMessage());
        }

        /** @var DbMetaDataHandler $oDbHandler */
        //$oDbHandler = oxNew('oxDbMetaDataHandler');
        //$oDbHandler->updateViews();

        self::clearTmp();

        return true;
    }

    /**
     * Check if provided path is inside eShop `tpm/` folder or use the `tmp/` folder path.
     *
     * @param string $sClearFolderPath
     *
     * @return string
     */
    protected static function _getFolderToClear($sClearFolderPath = '')
    {
        $sTempFolderPath = (string) Registry::getConfig()->getConfigParam('sCompileDir');

        if (!empty($sClearFolderPath) and (strpos($sClearFolderPath, $sTempFolderPath) !== false)) {
            $sFolderPath = $sClearFolderPath;
        } else {
            $sFolderPath = $sTempFolderPath;
        }

        return $sFolderPath;
    }


    /**
     * Check if resource could be deleted, then delete it's a file or
     * call recursive folder deletion if it's a directory.
     *
     * @param string $sFileName
     * @param string $sFilePath
     */
    protected static function _clear($sFileName, $sFilePath)
    {
        if (!in_array($sFileName, array('.', '..', '.gitkeep', '.htaccess'))) {
            if (is_file($sFilePath)) {
                @unlink($sFilePath);
            } else {
                self::clearTmp($sFilePath);
            }
        }
    }

    /**
     * Install/uninstall event.
     * Executes SQL queries from a shop specific file.
     *
     * @param string $sSqlFile      SQL file located in module docs folder (usually install.sql or uninstall.sql).
     * @param string $sFailureError An error message to show on failure.
     */
    protected static function _dbEventShopSpecific($sSqlFile, $sFailureError = 'Operation failed: ')
    {
        $sqls = file_get_contents(dirname(__FILE__) . '/../../installments/' . (string) $sSqlFile);
        $aShops = DatabaseProvider::getDb()->getAll('SELECT oxid FROM oxshops');

        // Iterate all SubShops
        foreach (explode(';', $sqls) as $sql) {
            $sql = trim($sql);
            if (!$sql) {
                continue;
            }

            self::_dbEventShopSpecificSql($sql, $aShops, $sFailureError);
        }
    }

    protected static function _dbEventShopSpecificSql($sql, $aShops, $sFailureError)
    {

        foreach ($aShops as $sShopId) {
            $sShopId = reset($sShopId);
            if (!$sShopId) {
                continue;
            }
            $sql = str_replace('#shop#', $sShopId, $sql);
            try {
                DatabaseProvider::getDb()->execute($sql);
            } catch (\Exception $ex) {
                error_log($sFailureError . $ex->getMessage());
            }
        }
    }

    private static function executeModuleMigrations(): void
    {
        $migrations = (new MigrationsBuilder())->build();

        $output = new BufferedOutput();
        $migrations->setOutput($output);
        $neeedsUpdate = $migrations->execute('migrations:up-to-date', 'osceasycredit');

        if ($neeedsUpdate) {
            $migrations->execute('migrations:migrate', 'osceasycredit');
        }
    }
}

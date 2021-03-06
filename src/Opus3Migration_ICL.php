<?php

/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the Cooperative Library Network Berlin-Brandenburg,
 * the Saarland University and State Library, the Saxon State Library -
 * Dresden State and University Library, the Bielefeld University Library and
 * the University Library of Hamburg University of Technology with funding from
 * the German Research Foundation and the European Regional Development Fund.
 *
 * LICENCE
 * OPUS is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or any later version.
 * OPUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with OPUS; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @category    Application
 * @package     Import
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2009, 2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

// Configure include path.
require_once dirname(__FILE__) . '/../common/bootstrap.php';
require_once 'Opus3Migration_Base.php';
set_include_path(
    '.' . PATH_SEPARATOR
    . PATH_SEPARATOR . dirname(dirname(dirname(__FILE__))) . '/scripts/migration/importer'
    . PATH_SEPARATOR . get_include_path()
);

require_once 'Opus3InstituteImport.php';
require_once 'Opus3CollectionsImport.php';
require_once 'Opus3SeriesImport.php';
require_once 'Opus3LicenceImport.php';
require_once 'Opus3RoleImport.php';


class Opus3Migration_ICL extends Opus3Migration_Base {

    private $_importFile;

    private $_importData;

    private $_stylesheet;

    private $_xslt;

    /**
     * Constructur.
     *
     * @param array $options Array with input options.
     */
    function __construct($options) {
        parent::__construct();
        if (array_key_exists('f', $options) !== false) {
            $this->_importFile = $options["f"];
        }
    }

    // Create Collections
    private function create_collection_roles() {

        $roles = array(
            "Collections" => array("name" => "collections", "position" => 9),
        );

        foreach ($roles as $r) {
            $role = new Opus_CollectionRole();
            $role->setName($r["name"]);
            $role->setOaiName($r["name"]);
            $role->setPosition($r["position"]);
            $role->setVisible(1);
            $role->setVisibleBrowsingStart(1);
            $role->setDisplayBrowsing('Name');
            $role->setVisibleFrontdoor(1);
            $role->setDisplayFrontdoor('Name');
            $role->setVisibleOai(1);
            $role->setDisplayOai('Name');
            $role->store();

            $root = $role->addRootCollection()->setVisible(1);
            $root->store();
        }

        $role = Opus_CollectionRole::fetchByName('institutes');
        $root = $role->addRootCollection()->setVisible(1);
        $root->store();
    }

    private function setStylesheet() {
        $this->_stylesheet = 'stylesheets';
        $this->_xslt = 'institute_structure.xslt';
    }

    // Import collections
    private function load_collections() {
        $import = new Opus3CollectionsImport($this->_importData);
        $import->start();
    }

    // Import series
    private function load_series() {
        $import = new Opus3SeriesImport($this->_importData);
        $import->start();
    }

    // Import faculties and institutes
    private function load_institutes() {
        $import = new Opus3InstituteImport($this->_importData, $this->_stylesheet, $this->_xslt);
        $import->start();
    }

    // Import Licences
    private function load_licences() {
        $import= new Opus3LicenceImport($this->_importData);
        $import->start();
    }

    // Import UserRoles
    private function load_roles() {
        $import= new Opus3RoleImport();
        $import->start();
    }

    // Import Fulltexts
    private function loadImportFile() {
        $this->_importData = new DOMDocument;
        $this->_importData->load($this->_importFile);
    }

   /**
     * Migrates OPUS3 to OPUS4
     *
     * @return void
     */
    public function run() {


         $this->setStylesheet();

        // Load Opus3-mySQL-XML-dump
         $this->loadImportFile();

        // Create Collection Roles
        $this->create_collection_roles();

        // Load Collections
        $this->load_collections();

        // Load Series
        $this->load_series();

        // Load Institutes
        $this->load_institutes();

        // Load Institutes
        $this->load_licences();

        // Load Roles
        $this->load_roles();
    }
}

echo "Run Opus3Migration_ICL"."\n";

// Bootstrap application.
$application = new Zend_Application(
    APPLICATION_ENV,
    array(
        "config"=>array(
            APPLICATION_PATH . '/application/configs/application.ini',
            APPLICATION_PATH . '/application/configs/config.ini',
            APPLICATION_PATH . '/application/configs/migration.ini',
            APPLICATION_PATH . '/application/configs/migration_config.ini'
        )
    )
);
$application->bootstrap(array('Configuration', 'Logging', 'Database'));


$options = getopt("f:");

// Start Opus3Migration
$migration = new Opus3Migration_ICL($options);
$migration->run();

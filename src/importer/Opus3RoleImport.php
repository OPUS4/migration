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
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2009-2011 OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: Opus3RoleImport.php 8423 2011-05-27 16:58:20Z sszott $
 */

class Opus3RoleImport {

   /**
    * Holds Zend-Configurationfile
    */
    protected $_config = null;

   /**
    * Holds Logger
    *
    */
    protected $_logger = null;

   /**
    * Holds Roles
    *
    */
    protected $_roles = array();

   /**
    * Holds Ips    *
    */
    protected $_ips = array();

    /**
     * Imports roles and ipranges from Opus3
     *
     */
    
    public function __construct() {
        $this->_config = Zend_Registry::get('Zend_Config');
        $this->_logger = Zend_Registry::get('Zend_Log');
        $this->_ips = $this->_config->migration->ip;
        $this->_roles = $this->_config->migration->role;
    }

    /**
     * Public Method for import of Roles and Ips
     *
     * @param void
     * @return void
     *
     */

    public function start() {
        $this->storeIps();
        $this->mapRoles();
    }

    private function storeIps() {
        try {
            if (count($this->_ips) > 0) {
                foreach ($this->_ips as $i) {
                    $ip = explode('-', $i->ip, 2);
                    $lower = "";
                    $upper = "";

                    if (count($ip) == 1) {
                        $lower = $ip[0];
                        $upper = $ip[0];
                    }
                    else if (count($ip) == 2) {
                        $lower = $ip[0];
                        $upper = $ip[1];
                    }
                    else {
                        throw new Exception("ERROR Opus3RoleImport: ".$i." is not a regular IP-Address or IP-Range\n");
                    }

                    $range = new Opus_Iprange();
                    $range->setStartingip($lower);
                    $range->setEndingip($upper);
                    $range->setName($i->name);
                    $range->store();
                }
            }
        }
        catch (Exception $e) {
            echo $e->getMessage();
        }
    }


    private function mapRoles() {

        $mf = $this->_config->migration->mapping->roles;
        $fp = null;
        try {
            $fp = @fopen($mf, 'w');
            if (!$fp) {
                throw new Exception("ERROR Opus3RoleImport: Could not create '".$mf."' for Roles.\n");
            }
        } catch (Exception $e){
            $this->_logger->log($e->getMessage(), Zend_Log::ERR);
            return;
        }

        try {
            if (count($this->_roles) > 0) {
                foreach ($this->_roles as $r) {
                    $name = $r->name;
                    $bereich = $r->bereich;

                    $role = null;
                    if (Opus_UserRole::fetchByname($name)) {
                        $role = Opus_UserRole::fetchByname($name);
                        $this->_logger->log("Role in DB found: " . $r->name, Zend_Log::DEBUG);
                    }
                    else {
                        $role = new Opus_UserRole();
                        $role->setName($r->name);
                        $role->store();
                        $this->_logger->log("Role imported: " . $r->name, Zend_Log::DEBUG);
                    }

                    $dbIps = Opus_Iprange::getAll();

                    if (count($r->ip) > 0) {
                        foreach ($r->ip as $roleIp) {
                            foreach ($dbIps as $dbIp) {
                                if ($roleIp == $dbIp->getDisplayName()) {
                                    $roles = array();
                                    $roles = $dbIp->getRole();
                                    array_push($roles, $role);
                                    $dbIp->setRole($roles);
                                    $dbIp->store();
                                }
                            }
                        }
                    }


                    fputs($fp, $r->bereich . ' ' .  $role->getId() . "\n");

                }
            }
        }
        catch (Exception $e){
            $this->_logger->log($e->getMessage(), Zend_Log::ERR);
        }

        fclose($fp);
    }

}
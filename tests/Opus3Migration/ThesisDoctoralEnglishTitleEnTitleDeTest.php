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
 * @category    TODO
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2008-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */


/**
 * Description:
 * - Dokumenttyp: Dissertation
 * - Sprache: english
 * - Originaltitel der Arbeit
 * - Titel der Arbeit in Englisch
 * - Titel der Arbeit in Deutsch
 * - Kurze Inhaltszusammenfassung in der Originalsprache (deutsch)
 * - Kurze Inhaltszusammenfassung in einer weiteren Sprache (english)
 *
 * @author gunar
 */
class Opus3Migration_ThesisDoctoralEnglishTitleEnTitleDeTest extends MigrationTestCase {

    protected $doc;

    public static function setUpBeforeClass()  {
        parent::setUpBeforeClass();
        parent::migrate("ThesisDoctoralEnglishTitleEnTitleDe.xml");
    }

    public function setUp() {
        parent::setUp();
        $this->doc = new Opus_Document(1);
    }

    public function testDoctypeDoctoralThesis() {
        $this->assertEquals($this->doc->getType(), 'doctoralthesis');
    }

    public function testTitleMainEnglish() {
        $this->assertEquals($this->doc->getTitleMain(0)->getValue(), 'English Title');
        $this->assertEquals($this->doc->getTitleMain(0)->getLanguage(), 'eng');
    }

    public function testTitleMainGerman() {
        $this->assertEquals($this->doc->getTitleMain(1)->getValue(), 'Deutscher Titel');
        $this->assertEquals($this->doc->getTitleMain(1)->getLanguage(), 'deu');
    }

    public function testTitleAbstractEnglish() {
        $this->assertEquals($this->doc->getTitleAbstract(0)->getValue(), 'Short Abstract');
        $this->assertEquals($this->doc->getTitleAbstract(0)->getLanguage(), 'eng');
    }

    public function testTitleAbstractGerman() {
        $this->assertEquals($this->doc->getTitleAbstract(1)->getValue(), 'Kurze Zusammenfassung');
        $this->assertEquals($this->doc->getTitleAbstract(1)->getLanguage(), 'deu');
    }

    public function testTitleAdditionalEnglish() {
        $this->assertEquals($this->doc->getTitleAdditional(0)->getValue(), 'Another english title');
        $this->assertEquals($this->doc->getTitleAdditional(0)->getLanguage(), 'eng');
    }

    public function testTitleAdditionalWarning() {
        $this->assertOutputContainsString("'title_en' or 'title_de' mapped to 'TitleAdditional' to prevent 'TitleMain' with duplicate language");
    }    
}
?>

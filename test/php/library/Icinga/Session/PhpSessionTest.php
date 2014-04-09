<?php
// {{{ICINGA_LICENSE_HEADER}}}
/**
 * This file is part of Icinga Web 2.
 *
 * Icinga Web 2 - Head for multiple monitoring backends.
 * Copyright (C) 2013 Icinga Development Team
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * @copyright  2013 Icinga Development Team <info@icinga.org>
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GPL, version 2
 * @author     Icinga Development Team <info@icinga.org>
 *
 */
// {{{ICINGA_LICENSE_HEADER}}}

namespace Tests\Icinga\Session;

use Icinga\Test\BaseTestCase;
use Icinga\Web\Session\PhpSession;

class PhpSessionTest extends BaseTestCase
{
    private function getSession()
    {
        if (!is_writable('/tmp')) {
            $this->markTestSkipped('Could not write to session directory');
        }
        return new PhpSession(
            array(
                'use_cookies'   => false,
                'save_path'     => '/tmp',
                'test_session_name' => 'IcingawebUnittest'
            )
        );
    }
    /**
     * Test the creation of a PhpSession object
     *
     * @runInSeparateProcess
     */
    public function testSessionCreation()
    {
        $this->getSession();
    }

    /**
     * Test PhpSession::open()
     *
     * @runInSeparateProcess
     */
    public function testSessionReadWrite()
    {
        $session = $this->getSession();
        $session->purge();
        $this->assertEquals(null, $session->get('key'));
        $session->set('key', 'value');
        $session->write();
        $session->read();
        $this->assertEquals('value', $session->get('key'));
    }

    /**
     * Test a session being closed by PhpSession::close()
     *
     * @runInSeparateProcess
     */
    public function testPurgeSession()
    {
        $session = $this->getSession();
        $session->set('key2', 'value2');
        $session->purge();
        $session->read();
        $this->assertEquals(null, $session->get('key2'));
    }

    /**
     * Test whether session namespaces are properly written and loaded
     *
     * @runInSeparateProcess
     */
    public function testNamespaceReadWrite()
    {
        $session = $this->getSession();
        $namespace = $session->getNamespace('test');
        $namespace->set('some_key', 'some_val');
        $namespace->set('an_array', array(1, 2, 3));
        $session->write();
        $session->clear();
        $session->read();
        $namespace = $session->getNamespace('test');
        $this->assertEquals($namespace->get('some_key'), 'some_val');
        $this->assertEquals($namespace->get('an_array'), array(1, 2, 3));
    }
}

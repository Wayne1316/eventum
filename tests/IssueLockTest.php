<?php

/*
 * This file is part of the Eventum (Issue Tracking System) package.
 *
 * @copyright (c) Eventum Team
 * @license GNU General Public License, version 2 or later (GPL-2+)
 *
 * For the full copyright and license information,
 * please see the COPYING and AUTHORS files
 * that were distributed with this source code.
 */

class IssueLockTest extends TestCase
{
    /**
     * @group slow
     */
    public function testLock()
    {
        $issue_id = 1;
        $locker = 'admin';
        $locker2 = 'user';

        $setup = Setup::get();
        $setup['issue_lock'] = 2;

        $res = Issue_Lock::acquire($issue_id, $locker);
        $this->assertTrue($res);

        // lock retry with same user, gives true
        $res = Issue_Lock::acquire($issue_id, $locker);
        $this->assertTrue($res);

        // get lock with another user, gives false
        $res = Issue_Lock::acquire($issue_id, $locker2);
        $this->assertFalse($res);

        sleep(2);
        // lock has expired
        $res = Issue_Lock::acquire($issue_id, $locker);
        $this->assertTrue($res);

        $info = Issue_Lock::getInfo($issue_id);
        $this->assertNotEmpty($info);
        $this->assertArrayHasKey('expires', $info);
        $this->assertArrayHasKey('usr_id', $info);
        $this->assertEquals($locker, $info['usr_id']);
    }
}

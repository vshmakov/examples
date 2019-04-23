<?php

namespace App\Tests\Functional\Profile;

use App\DataFixtures\UserFixtures;

final class TeacherManipulateProfilesTest extends StudentManipulateProfilesTest
{
    public static function setUpBeforeClass(): void
    {
        self::$studentClient = self::createAuthenticatedClient(UserFixtures::TEACHER_USERNAME);
    }
}

<?php

namespace App\Tests\Functional\Student;

use App\DataFixtures\UserFixtures;
use App\Request\Http\Method;
use App\Tests\Functional\BaseWebTestCase;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\DomCrawler\Crawler;

final class TeacherShowsStudentsTest extends BaseWebTestCase
{
    /** @var Client */
    private static $teacherClient;

    public static function setUpBeforeClass(): void
    {
        self::$teacherClient = self::createAuthenticatedClient(UserFixtures::TEACHER_USERNAME);
    }

    /**
     * @test
     */
    public function teacherEntersToStudentIndexPage(): Crawler
    {
        $studentIndexPageCrawler = self::$teacherClient->request(Method::GET, '/student/');
        $this->assertResponseIsSuccessful(self::$teacherClient);

        return $studentIndexPageCrawler;
    }

    /**
     * @test
     * @depends  teacherEntersToStudentIndexPage
     */
    public function teacherHasStudent(Crawler $studentIndexPageCrawler): Crawler
    {
        $studentCrawler = $studentIndexPageCrawler->filter('tbody tr:first-child');

        $studentUsername = $studentCrawler->filter('.username')->text();
        $this->assertSame(UserFixtures::STUDENT_USERNAME, $studentUsername);

        return $studentCrawler;
    }

    /**
     * @test
     * @depends  teacherHasStudent
     */
    public function teacherEntersToStudentAttemptsPage(Crawler $studentCrawler): void
    {
        self::$teacherClient->click($studentCrawler->selectLink('Посмотреть список попыток')->link());
        $this->assertResponseIsSuccessful(self::$teacherClient);
    }

    /**
     * @test
     * @depends  teacherHasStudent
     */
    public function teacherEntersToStudentExamplesPage(Crawler $studentCrawler): void
    {
        self::$teacherClient->click($studentCrawler->selectLink('Посмотреть список примеров')->link());
        $this->assertResponseIsSuccessful(self::$teacherClient);
    }

    /**
     * @test
     * @depends teacherEntersToStudentAttemptsPage
     */
    public function teacherGetsAttemptsOfStudent(): void
    {
        self::ajaxGet(self::$teacherClient, '/api/attempts.jsondt', ['draw' => 1, 'username' => UserFixtures::STUDENT_USERNAME]);
        $this->assertResponseIsSuccessful(self::$teacherClient);
    }

    /**
     * @test
     * @depends teacherEntersToStudentExamplesPage
     */
    public function teacherGetsExamplesOfStudent(): void
    {
        self::ajaxGet(self::$teacherClient, '/api/examples.jsondt', ['draw' => 1, 'username' => UserFixtures::STUDENT_USERNAME]);
        $this->assertResponseIsSuccessful(self::$teacherClient);
    }
}

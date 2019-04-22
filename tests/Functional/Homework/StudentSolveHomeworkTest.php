<?php

declare(strict_types=1);

namespace App\Tests\Functional\Homework;

use App\DataFixtures\Attempt\ProfileFixtures;
use App\DataFixtures\UserFixtures;
use App\Request\Http\Method;
use App\Tests\Functional\Attempt\AbstractSolveAttemptTest;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Client;

final class StudentSolveHomeworkTest extends AbstractSolveAttemptTest
{
    /** @var Client */
    private static $StudentClient;

    public static function setUpBeforeClass(): void
    {
        self::$StudentClient = self::createAuthenticatedClient(UserFixtures::STUDENT_USERNAME);
    }

    /**
     * @test
     */
    public function studentEntersToHomeworkIndexPage(): Crawler
    {
        $homeworkIndexPageCrawler = self::$StudentClient->request(Method::GET, '/homework/');
        $this->assertResponseIsSuccessful(self::$StudentClient);

        return $homeworkIndexPageCrawler;
    }

    /**
     * @test
     * @depends studentEntersToHomeworkIndexPage
     */
    public function studentShowsActualHomework(Crawler $homeworkIndexPageCrawler): Crawler
    {
        $firstHomeworkCrawler = $homeworkIndexPageCrawler->filter('.actual tbody tr:first-child');
        $this->assertNotEmpty($firstHomeworkCrawler);

        return $firstHomeworkCrawler;
    }

    /**
     * @test
     * @depends studentShowsActualHomework
     */
    public function studentShowsHomeworkSettings(Crawler $homeworkCrawler): void
    {
        self::$StudentClient->click(
            $homeworkCrawler->selectLink(ProfileFixtures::ADDITION_PROFILE_REFERENCE)->link()
        );
        $this->assertResponseIsSuccessful(self::$StudentClient);
    }

    /**
     * @test
     * @depends  studentShowsActualHomework
     * @depends  studentShowsHomeworkSettings
     */
    public function studentSolvesHomework(Crawler $homeworkCrawler): void
    {
        self::$StudentClient->click(
            $homeworkCrawler->selectLink('Выполнить')->link()
        );
        $this->assertRedirectionLocationMatch('#/attempt/\d+/$#', self::$StudentClient);
    }

    /**
     * @test
     * @depends studentSolvesHomework
     */
    public function studentSolvesHomeworkAttempt(): void
    {
        $attemptId = $this->getAttemptId(self::$StudentClient);
        $attemptData = $this->getAttemptData($attemptId, self::$StudentClient);
        $this->solveAttempt($attemptData, self::$StudentClient);
    }

    /**
     * @test
     * @depends studentSolvesHomeworkAttempt
     */
    public function studentSeesArchivedHomework(): void
    {
        $homeworkIndexPageCrawler = $this->studentEntersToHomeworkIndexPage();
        $this->assertNotEmpty($homeworkIndexPageCrawler->filter('.archive tbody tr'));
    }

    private function getHomeworkId(Crawler $solveHomeworkLinkCrawler): int
    {
        $this->assertTrue((bool) preg_match('#/homework/(?<taskId>\d+)/solve/$#', $solveHomeworkLinkCrawler->attr('href'), $matches));

        return $matches['taskId'];
    }
}

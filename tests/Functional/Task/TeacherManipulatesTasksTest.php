<?php

declare(strict_types=1);

namespace App\Tests\Functional\Task;

use App\DataFixtures\Attempt\ProfileFixtures;
use App\DataFixtures\UserFixtures;
use App\Request\Http\Method;
use App\Tests\Functional\BaseWebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Client;

final class TeacherManipulatesTasksTest extends BaseWebTestCase
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
    public function teacherEntersToTaskIndexPage(): Crawler
    {
        $taskIndexPageCrawler = self::$teacherClient->request(Method::GET, '/task/');
        $this->assertResponseIsSuccessful(self::$teacherClient);

        return $taskIndexPageCrawler;
    }

    /**
     * @test
     * @depends  teacherEntersToTaskIndexPage
     */
    public function teacherHasTask(Crawler $taskIndexPageCrawler): Crawler
    {
        $firstActualTaskCrawler = $taskIndexPageCrawler->filter('.actual tbody tr:first-child');
        $this->assertNotEmpty($firstActualTaskCrawler);

        return $firstActualTaskCrawler;
    }

    /**
     * @test
     * @depends  teacherHasTask
     */
    public function taskHasShowLink(Crawler $firstActualTaskCrawler): Crawler
    {
        $showTaskLinkCrawler = $firstActualTaskCrawler->filter('a:first-child');
        $this->assertNotEmpty($showTaskLinkCrawler);

        return $showTaskLinkCrawler;
    }

    /**
     * @test
     * @depends  taskHasShowLink
     */
    public function defaultTaskHasAdditionSettings(Crawler $taskCrawler): void
    {
        $this->assertSame(ProfileFixtures::ADDITION_PROFILE_REFERENCE, $taskCrawler->text());
    }

    /**
     * @test
     * @depends  taskHasShowLink
     * @depends  defaultTaskHasAdditionSettings
     */
    public function teacherEntersToTaskShowPage(Crawler $showTaskLinkCrawler): Crawler
    {
        $showTaskPageCrawler = self::$teacherClient->click($showTaskLinkCrawler->link());
        $this->assertResponseIsSuccessful(self::$teacherClient);

        return $showTaskPageCrawler;
    }

    /**
     * @test
     * @depends  teacherEntersToTaskShowPage
     */
    public function taskHasContractor(Crawler $showTaskPageCrawler): Crawler
    {
        $firstContractorCrawler = $showTaskPageCrawler->filter('#active-contractors tbody tr:first-child');
        $this->assertNotEmpty($firstContractorCrawler);

        return $firstContractorCrawler;
    }

    /**
     * @test
     * @depends  taskHasContractor
     */
    public function teacherEntersToContractorAttemptsPage(Crawler $contractorCrawler): void
    {
        self::$teacherClient->click(
            $contractorCrawler->selectLink('Просмотреть попытки')->link()
        );
        $this->assertResponseIsSuccessful(self::$teacherClient);
    }

    /**
     * @test
     * @depends  taskHasContractor
     */
    public function teacherEntersToContractorExamplesPage(Crawler $contractorCrawler): void
    {
        self::$teacherClient->click(
            $contractorCrawler->selectLink('Просмотреть примеры')->link()
        );
        $this->assertResponseIsSuccessful(self::$teacherClient);
    }

    /**
     * @test
     * @depends  taskHasShowLink
     */
    public function teacherGetsContractorAttempts(Crawler $showTaskLinkCrawler): array
    {
        $filterParameters = ['draw' => 1, 'username' => UserFixtures::STUDENT_USERNAME, 'task' => $this->getTaskId($showTaskLinkCrawler)];
        self::ajaxGet(self::$teacherClient, '/api/attempts.jsondt', $filterParameters);
        $this->assertResponseIsSuccessful(self::$teacherClient);

        return $filterParameters;
    }

    /**
     * @test
     * @depends  teacherGetsContractorAttempts
     */
    public function teacherGetsContractorExamples(array $filterParameters): void
    {
        self::ajaxGet(self::$teacherClient, '/api/examples.jsondt', $filterParameters);
        $this->assertResponseIsSuccessful(self::$teacherClient);
    }

    /**
     * @test
     * @depends  teacherEntersToTaskIndexPage
     */
    public function teacherCreatesTask(Crawler $taskIndexPageCrawler): string
    {
        $createTaskPageCrawler = self::$teacherClient->click(
            $taskIndexPageCrawler->selectLink('Создать задание')->link()
        );
        $this->assertResponseIsSuccessful(self::$teacherClient);

        self::$teacherClient->submit(
            $createTaskPageCrawler->selectButton('Сохранить')->form()
        );
        $this->assertTrue(self::$teacherClient->getResponse()->isRedirect('/task/'));

        return ProfileFixtures::GUEST_PROFILE_DESCRIPTION;
    }

    /**
     * @test
     * @depends  teacherCreatesTask
     */
    public function taskHasDefaultSettingsDescription(string $createdTaskSettingsDescription): Crawler
    {
        $taskIndexPageCrawler = $this->teacherEntersToTaskIndexPage();
        $firstTaskCrawler = $this->teacherHasTask($taskIndexPageCrawler);
        $showTaskCrawler = $this->taskHasShowLink($firstTaskCrawler);
        $this->assertSame($createdTaskSettingsDescription, $showTaskCrawler->text());

        return $firstTaskCrawler;
    }

    /**
     * @test
     * @depends  taskHasDefaultSettingsDescription
     */
    public function teacherEntersToEditTaskPage(Crawler $firstTaskCrawler): Crawler
    {
        $editTaskPageCrawler = self::$teacherClient->click(
            $firstTaskCrawler->selectLink('Редактировать')->link()
        );
        $this->assertResponseIsSuccessful(self::$teacherClient);

        return $editTaskPageCrawler;
    }

    /**
     * @test
     * @depends  teacherEntersToEditTaskPage
     */
    public function teacherEditsTask(Crawler $editTaskPageCrawler): void
    {
        self::$teacherClient->submit(
            $editTaskPageCrawler->selectButton('Сохранить')->form()
        );
        $this->assertTrue(self::$teacherClient->getResponse()->isRedirect('/task/'));
    }

    /**
     * @test
     * @depends  taskHasDefaultSettingsDescription
     */
    public function teacherArchivesTask(Crawler $taskCrawler): Crawler
    {
        self::$teacherClient->click(
            $taskCrawler->selectLink('Архивировать')->link()
        );
        $this->assertTrue(self::$teacherClient->getResponse()->isRedirect('/task/'));

        return self::$teacherClient->followRedirect();
    }

    private function getTaskId(Crawler $showTaskLinkCrawler): int
    {
        $this->assertTrue((bool) preg_match('#/task/(?<taskId>\d+)/$#', $showTaskLinkCrawler->attr('href'), $matches));

        return (int) $matches['taskId'];
    }
}

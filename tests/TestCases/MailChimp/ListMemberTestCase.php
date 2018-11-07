<?php
declare(strict_types=1);

namespace Tests\App\TestCases\MailChimp;

use App\Database\Entities\MailChimp\MailChimpListMember;
use Illuminate\Http\JsonResponse;
use Mailchimp\Mailchimp;
use Mockery;
use Mockery\MockInterface;
use Tests\App\TestCases\WithDatabaseTestCase;

abstract class ListMemberTestCase extends WithDatabaseTestCase
{
    protected const MAILCHIMP_EXCEPTION_MESSAGE = 'MailChimp exception';

    /**
     * @var string
     */
    protected $createdListId;

    /**
     * @var array
     */
    protected $createdListMemberIds = [];

    /**
     * @var array
     */
    protected static $listData = [
        'name' => 'New list',
        'permission_reminder' => 'You signed up for updates on Greeks economy.',
        'email_type_option' => false,
        'contact' => [
            'company' => 'Doe Ltd.',
            'address1' => 'DoeStreet 1',
            'address2' => '',
            'city' => 'Doesy',
            'state' => 'Doedoe',
            'zip' => '1672-12',
            'country' => 'US',
            'phone' => '55533344412'
        ],
        'campaign_defaults' => [
            'from_name' => 'John Doe',
            'from_email' => 'john@doe.com',
            'subject' => 'My new campaign!',
            'language' => 'US'
        ],
        'visibility' => 'prv',
        'use_archive_bar' => false,
        'notify_on_subscribe' => 'notify@loyaltycorp.com.au',
        'notify_on_unsubscribe' => 'notify@loyaltycorp.com.au'
    ];

    /**
     * @var array
     */
    protected static $listMemberData = [
        'email_address' => 'rhapgutz@yahoo.com',
        'email_type' => 'html',
        'status' => 'subscribed'
    ];

    /**
     * @var array
     */
    protected static $notRequired = [
        'email_type',
        'language',
        'vip',
        'location',
        'tags'
    ];

    /**
     * Call MailChimp to delete list created during test.
     *
     * @return void
     */
    public function tearDown(): void
    {
        if ($this->createdListId) {
            /** @var Mailchimp $mailChimp */
            $mailChimp = $this->app->make(Mailchimp::class);
            // Delete list on MailChimp after test
            $mailChimp->delete(\sprintf('lists/%s', $this->createdListId));
        }

        parent::tearDown();
    }

    /**
     * Asserts error response when list member not found.
     *
     * @param string $listMemberId
     *
     * @return void
     */
    protected function assertListMemberNotFoundResponse(string $listMemberId): void
    {
        $content = \json_decode($this->response->content(), true);

        $this->assertResponseStatus(404);
        self::assertArrayHasKey('message', $content);
        self::assertEquals(\sprintf('MailChimpListMember[%s] not found', $listMemberId), $content['message']);
    }

    /**
     * Asserts error response when MailChimp exception is thrown.
     *
     * @param \Illuminate\Http\JsonResponse $response
     *
     * @return void
     */
    protected function assertMailChimpExceptionResponse(JsonResponse $response): void
    {
        $content = \json_decode($response->content(), true);

        self::assertEquals(400, $response->getStatusCode());
        self::assertArrayHasKey('message', $content);
        self::assertEquals(self::MAILCHIMP_EXCEPTION_MESSAGE, $content['message']);
    }

    /**
     * Create MailChimp list member into database.
     *
     * @param array $data
     *
     * @return \App\Database\Entities\MailChimp\MailChimpListMember
     */
    protected function createListMember(array $data): MailChimpListMember
    {
        $listMember = new MailChimpListMember($data);

        $this->entityManager->persist($listMember);
        $this->entityManager->flush();

        return $listMember;
    }

    /**
     * Returns mock of MailChimp to trow exception when requesting their API.
     *
     * @param string $method
     *
     * @return \Mockery\MockInterface
     *
     * @SuppressWarnings(PHPMD.StaticAccess) Mockery requires static access to mock()
     */
    protected function mockMailChimpForException(string $method): MockInterface
    {
        $mailChimp = Mockery::mock(Mailchimp::class);

        $mailChimp
            ->shouldReceive($method)
            ->once()
            ->withArgs(function (string $method, ?array $options = null) {
                return !empty($method) && (null === $options || \is_array($options));
            })
            ->andThrow(new \Exception(self::MAILCHIMP_EXCEPTION_MESSAGE));

        return $mailChimp;
    }
}

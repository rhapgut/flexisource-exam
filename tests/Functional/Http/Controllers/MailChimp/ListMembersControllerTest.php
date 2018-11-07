<?php
declare(strict_types=1);

namespace Tests\App\Functional\Http\Controllers\MailChimp;

use Tests\App\TestCases\MailChimp\ListMemberTestCase;

class ListMembersControllerTest extends ListMemberTestCase
{
    /**
     * @var string
     */
    protected $listId;

    /** @inheritdoc */
    public function setUp(): void
    {
        parent::setUp();
        $this->post('/mailchimp/lists/', static::$listData); // create list

        $listData = \json_decode($this->response->getContent(), true);
        $this->listId = $listData['list_id'];
        $this->createdListId = $listData['mail_chimp_id'];
    }

    /**
     * Test application add successfully list member and returns it back with id from MailChimp.
     *
     * @return void
     */
    public function testCreateListMemberSuccessfully(): void
    {
        $listMemberData = static::$listMemberData;
        $listMemberData['list_id'] = $this->listId;

        $this->post(\sprintf('/mailchimp/lists/%s/members', $this->listId), $listMemberData);

        $content = \json_decode($this->response->getContent(), true);

        $this->assertResponseOk();
        $this->seeJson(static::$listMemberData);
        self::assertArrayHasKey('mail_chimp_id', $content);
        self::assertNotNull($content['mail_chimp_id']);
    }

    /**
     * Test application returns error response with errors when list member validation fails.
     *
     * @return void
     */
    public function testCreateListMemberValidationFailed(): void
    {
        $this->post(\sprintf('/mailchimp/lists/%s/members', $this->listId));

        $content = \json_decode($this->response->getContent(), true);

        $this->assertResponseStatus(400);
        self::assertArrayHasKey('message', $content);
        self::assertArrayHasKey('errors', $content);
        self::assertEquals('Invalid data given', $content['message']);

        foreach (\array_keys(static::$listMemberData) as $key) {
            if (\in_array($key, static::$notRequired, true)) {
                continue;
            }

            self::assertArrayHasKey($key, $content['errors']);
        }
    }

    /**
     * Test application returns error response when list member not found.
     *
     * @return void
     */
    public function testRemoveListMemberNotFoundException(): void
    {
        $this->delete(\sprintf('/mailchimp/lists/%s/members/invalid-list-member-id', $this->listId));

        $this->assertListMemberNotFoundResponse('invalid-list-member-id');
    }

    /**
     * Test application returns empty successful response when removing existing list member.
     *
     * @return void
     */
    public function testRemoveListMemberSuccessfully(): void
    {
        $listMemberData = static::$listMemberData;
        $listMemberData['list_id'] = $this->listId;
        $this->post(\sprintf('/mailchimp/lists/%s/members', $this->listId), $listMemberData);

        $member = \json_decode($this->response->content(), true);
        $this->delete(\sprintf('/mailchimp/lists/%s/members/%s', $member['list_id'], $member['list_member_id']));

        $this->assertResponseOk();
        self::assertEmpty(\json_decode($this->response->content(), true));
    }

    /**
     * Test application returns error response when list member not found.
     *
     * @return void
     */
    public function testShowListMemberNotFoundException(): void
    {
        $this->get(\sprintf('/mailchimp/lists/%s/members/invalid-list-member-id', $this->listId));

        $this->assertListMemberNotFoundResponse('invalid-list-member-id');
    }

    /**
     * Test application returns successful response with list member data when requesting existing list member.
     *
     * @return void
     */
    public function testShowListMemberSuccessfully(): void
    {
        $listMemberData = static::$listMemberData;
        $listMemberData['list_id'] = $this->listId;
        $member = $this->createListMember($listMemberData);

        $this->get(\sprintf('/mailchimp/lists/%s/members/%s', $this->listId, $member->getId()));
        $content = \json_decode($this->response->content(), true);

        $this->assertResponseOk();

        foreach (static::$listMemberData as $key => $value) {
            self::assertArrayHasKey($key, $content);
            self::assertEquals($value, $content[$key]);
        }
    }

    /**
     * Test application returns error response when list member not found.
     *
     * @return void
     */
    public function testUpdateListMemberNotFoundException(): void
    {
        $this->put(\sprintf('/mailchimp/lists/%s/members/invalid-list-member-id', $this->listId));

        $this->assertListMemberNotFoundResponse('invalid-list-member-id');
    }

    /**
     * Test application returns successfully response when updating existing list member with updated values.
     *
     * @return void
     */
    public function testUpdateListMemberSuccessfully(): void
    {
        $listMemberData = static::$listMemberData;
        $listMemberData['list_id'] = $this->listId;

        $this->post(\sprintf('/mailchimp/lists/%s/members', $this->listId), $listMemberData);
        $member = \json_decode($this->response->getContent(), true);

        $this->put(\sprintf('/mailchimp/lists/%s/members/%s', $member['list_id'], $member['list_member_id']), ['email_type' => 'text']);
        $content = \json_decode($this->response->content(), true);

        $this->assertResponseOk();

        foreach (\array_keys(static::$listMemberData) as $key) {
            self::assertArrayHasKey($key, $content);
            self::assertEquals('text', $content['email_type']);
        }
    }

    /**
     * Test application returns error response with errors when list member validation fails.
     *
     * @return void
     */
    public function testUpdateListMemberValidationFailed(): void
    {
        $listMemberData = static::$listMemberData;
        $listMemberData['list_id'] = $this->listId;
        $member = $this->createListMember($listMemberData);

        $this->put(\sprintf('/mailchimp/lists/%s/members/%s', $this->listId, $member->getId()), ['email_type' => 'invalid']);
        $content = \json_decode($this->response->content(), true);

        $this->assertResponseStatus(400);
        self::assertArrayHasKey('message', $content);
        self::assertArrayHasKey('errors', $content);
        self::assertArrayHasKey('email_type', $content['errors']);
        self::assertEquals('Invalid data given', $content['message']);
    }
}

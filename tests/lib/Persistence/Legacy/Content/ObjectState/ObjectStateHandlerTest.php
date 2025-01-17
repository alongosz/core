<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Core\Persistence\Legacy\Content\ObjectState;

use Ibexa\Contracts\Core\Persistence\Content\ObjectState;
use Ibexa\Contracts\Core\Persistence\Content\ObjectState\Group;
use Ibexa\Contracts\Core\Persistence\Content\ObjectState\InputStruct;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Core\Persistence\Legacy\Content\ObjectState\Gateway;
use Ibexa\Core\Persistence\Legacy\Content\ObjectState\Handler;
use Ibexa\Core\Persistence\Legacy\Content\ObjectState\Mapper;
use Ibexa\Tests\Core\Persistence\Legacy\Content\LanguageAwareTestCase;
use Ibexa\Tests\Integration\Core\Repository\BaseTest as APIBaseTest;

/**
 * @covers \Ibexa\Core\Persistence\Legacy\Content\ObjectState\Handler
 */
class ObjectStateHandlerTest extends LanguageAwareTestCase
{
    /**
     * Object state handler.
     *
     * @var \Ibexa\Core\Persistence\Legacy\Content\ObjectState\Handler
     */
    protected $objectStateHandler;

    /**
     * Object state gateway mock.
     *
     * @var \Ibexa\Core\Persistence\Legacy\Content\ObjectState\Gateway
     */
    protected $gatewayMock;

    /**
     * Object state mapper mock.
     *
     * @var \Ibexa\Core\Persistence\Legacy\Content\ObjectState\Mapper
     */
    protected $mapperMock;

    public function testCreateGroup()
    {
        $handler = $this->getObjectStateHandler();
        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $mapperMock->expects($this->once())
            ->method('createObjectStateGroupFromInputStruct')
            ->with($this->equalTo($this->getInputStructFixture()))
            ->will($this->returnValue($this->getObjectStateGroupFixture()));

        $gatewayMock->expects($this->once())
            ->method('insertObjectStateGroup')
            ->with($this->equalTo($this->getObjectStateGroupFixture()))
            ->will($this->returnValue($this->getObjectStateGroupFixture()));

        $result = $handler->createGroup($this->getInputStructFixture());

        $this->assertInstanceOf(
            Group::class,
            $result
        );
    }

    public function testLoadGroup()
    {
        $handler = $this->getObjectStateHandler();
        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects($this->once())
            ->method('loadObjectStateGroupData')
            ->with($this->equalTo(2))
            ->will($this->returnValue([[]]));

        $mapperMock->expects($this->once())
            ->method('createObjectStateGroupFromData')
            ->with($this->equalTo([[]]))
            ->will($this->returnValue($this->getObjectStateGroupFixture()));

        $result = $handler->loadGroup(2);

        $this->assertInstanceOf(
            Group::class,
            $result
        );
    }

    public function testLoadGroupThrowsNotFoundException()
    {
        $this->expectException(NotFoundException::class);

        $handler = $this->getObjectStateHandler();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects($this->once())
            ->method('loadObjectStateGroupData')
            ->with($this->equalTo(APIBaseTest::DB_INT_MAX))
            ->will($this->returnValue([]));

        $handler->loadGroup(APIBaseTest::DB_INT_MAX);
    }

    public function testLoadGroupByIdentifier()
    {
        $handler = $this->getObjectStateHandler();
        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects($this->once())
            ->method('loadObjectStateGroupDataByIdentifier')
            ->with($this->equalTo('ez_lock'))
            ->will($this->returnValue([[]]));

        $mapperMock->expects($this->once())
            ->method('createObjectStateGroupFromData')
            ->with($this->equalTo([[]]))
            ->will($this->returnValue($this->getObjectStateGroupFixture()));

        $result = $handler->loadGroupByIdentifier('ez_lock');

        $this->assertInstanceOf(
            Group::class,
            $result
        );
    }

    public function testLoadGroupByIdentifierThrowsNotFoundException()
    {
        $this->expectException(NotFoundException::class);

        $handler = $this->getObjectStateHandler();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects($this->once())
            ->method('loadObjectStateGroupDataByIdentifier')
            ->with($this->equalTo('unknown'))
            ->will($this->returnValue([]));

        $handler->loadGroupByIdentifier('unknown');
    }

    public function testLoadAllGroups()
    {
        $handler = $this->getObjectStateHandler();
        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects($this->once())
            ->method('loadObjectStateGroupListData')
            ->with($this->equalTo(0), $this->equalTo(-1))
            ->will($this->returnValue([[]]));

        $mapperMock->expects($this->once())
            ->method('createObjectStateGroupListFromData')
            ->with($this->equalTo([[]]))
            ->will($this->returnValue([$this->getObjectStateGroupFixture()]));

        $result = $handler->loadAllGroups();

        foreach ($result as $resultItem) {
            $this->assertInstanceOf(
                Group::class,
                $resultItem
            );
        }
    }

    public function testLoadObjectStates()
    {
        $handler = $this->getObjectStateHandler();
        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects($this->once())
            ->method('loadObjectStateListData')
            ->with($this->equalTo(2))
            ->will($this->returnValue([[]]));

        $mapperMock->expects($this->once())
            ->method('createObjectStateListFromData')
            ->with($this->equalTo([[]]))
            ->will($this->returnValue([$this->getObjectStateFixture(), $this->getObjectStateFixture()]));

        $result = $handler->loadObjectStates(2);

        foreach ($result as $resultItem) {
            $this->assertInstanceOf(
                ObjectState::class,
                $resultItem
            );
        }
    }

    public function testUpdateGroup()
    {
        $handler = $this->getObjectStateHandler();
        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $mapperMock->expects($this->once())
            ->method('createObjectStateGroupFromInputStruct')
            ->with($this->equalTo($this->getInputStructFixture()))
            ->will($this->returnValue($this->getObjectStateGroupFixture()));

        $gatewayMock->expects($this->once())
            ->method('updateObjectStateGroup')
            ->with($this->equalTo(new Group(['id' => 2])));

        $gatewayMock->expects($this->once())
            ->method('loadObjectStateGroupData')
            ->with($this->equalTo(2))
            ->will($this->returnValue([[]]));

        $mapperMock->expects($this->once())
            ->method('createObjectStateGroupFromData')
            ->with($this->equalTo([[]]))
            ->will($this->returnValue($this->getObjectStateGroupFixture()));

        $result = $handler->updateGroup(2, $this->getInputStructFixture());

        $this->assertInstanceOf(
            Group::class,
            $result
        );
    }

    public function testDeleteGroup()
    {
        $handler = $this->getObjectStateHandler();
        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects($this->once())
            ->method('loadObjectStateListData')
            ->with($this->equalTo(2))
            ->will($this->returnValue([[]]));

        $mapperMock->expects($this->once())
            ->method('createObjectStateListFromData')
            ->with($this->equalTo([[]]))
            ->will(
                $this->returnValue(
                    [
                        new ObjectState(['id' => 1]),
                        new ObjectState(['id' => 2]),
                    ]
                )
            );

        $gatewayMock->expects($this->exactly(2))
            ->method('deleteObjectStateLinks');

        $gatewayMock->expects($this->exactly(2))
            ->method('deleteObjectState');

        $gatewayMock->expects($this->at(1))
            ->method('deleteObjectStateLinks')
            ->with($this->equalTo(1));

        $gatewayMock->expects($this->at(2))
            ->method('deleteObjectState')
            ->with($this->equalTo(1));

        $gatewayMock->expects($this->at(3))
            ->method('deleteObjectStateLinks')
            ->with($this->equalTo(2));

        $gatewayMock->expects($this->at(4))
            ->method('deleteObjectState')
            ->with($this->equalTo(2));

        $gatewayMock->expects($this->once())
            ->method('deleteObjectStateGroup')
            ->with($this->equalTo(2));

        $handler->deleteGroup(2);
    }

    public function testCreate()
    {
        $handler = $this->getObjectStateHandler();
        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $mapperMock->expects($this->once())
            ->method('createObjectStateFromInputStruct')
            ->with($this->equalTo($this->getInputStructFixture()))
            ->will($this->returnValue($this->getObjectStateFixture()));

        $gatewayMock->expects($this->once())
            ->method('insertObjectState')
            ->with($this->equalTo($this->getObjectStateFixture()), $this->equalTo(2))
            ->will($this->returnValue($this->getObjectStateFixture()));

        $result = $handler->create(2, $this->getInputStructFixture());

        $this->assertInstanceOf(
            ObjectState::class,
            $result
        );
    }

    public function testLoad()
    {
        $handler = $this->getObjectStateHandler();
        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects($this->once())
            ->method('loadObjectStateData')
            ->with($this->equalTo(1))
            ->will($this->returnValue([[]]));

        $mapperMock->expects($this->once())
            ->method('createObjectStateFromData')
            ->with($this->equalTo([[]]))
            ->will($this->returnValue($this->getObjectStateFixture()));

        $result = $handler->load(1);

        $this->assertInstanceOf(
            ObjectState::class,
            $result
        );
    }

    public function testLoadThrowsNotFoundException()
    {
        $this->expectException(NotFoundException::class);

        $handler = $this->getObjectStateHandler();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects($this->once())
            ->method('loadObjectStateData')
            ->with($this->equalTo(APIBaseTest::DB_INT_MAX))
            ->will($this->returnValue([]));

        $handler->load(APIBaseTest::DB_INT_MAX);
    }

    public function testLoadByIdentifier()
    {
        $handler = $this->getObjectStateHandler();
        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects($this->once())
            ->method('loadObjectStateDataByIdentifier')
            ->with($this->equalTo('not_locked'), $this->equalTo(2))
            ->will($this->returnValue([[]]));

        $mapperMock->expects($this->once())
            ->method('createObjectStateFromData')
            ->with($this->equalTo([[]]))
            ->will($this->returnValue($this->getObjectStateFixture()));

        $result = $handler->loadByIdentifier('not_locked', 2);

        $this->assertInstanceOf(
            ObjectState::class,
            $result
        );
    }

    public function testLoadByIdentifierThrowsNotFoundException()
    {
        $this->expectException(NotFoundException::class);

        $handler = $this->getObjectStateHandler();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects($this->once())
            ->method('loadObjectStateDataByIdentifier')
            ->with($this->equalTo('unknown'), $this->equalTo(2))
            ->will($this->returnValue([]));

        $handler->loadByIdentifier('unknown', 2);
    }

    public function testUpdate()
    {
        $handler = $this->getObjectStateHandler();
        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $mapperMock->expects($this->once())
            ->method('createObjectStateFromInputStruct')
            ->with($this->equalTo($this->getInputStructFixture()))
            ->will($this->returnValue($this->getObjectStateFixture()));

        $gatewayMock->expects($this->once())
            ->method('updateObjectState')
            ->with($this->equalTo(new ObjectState(['id' => 1])));

        $gatewayMock->expects($this->once())
            ->method('loadObjectStateData')
            ->with($this->equalTo(1))
            ->will($this->returnValue([[]]));

        $mapperMock->expects($this->once())
            ->method('createObjectStateFromData')
            ->with($this->equalTo([[]]))
            ->will($this->returnValue($this->getObjectStateFixture()));

        $result = $handler->update(1, $this->getInputStructFixture());

        $this->assertInstanceOf(
            ObjectState::class,
            $result
        );
    }

    public function testSetPriority()
    {
        $handler = $this->getObjectStateHandler();
        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects($this->once())
            ->method('loadObjectStateData')
            ->with($this->equalTo(2))
            ->will($this->returnValue([[]]));

        $mapperMock->expects($this->once())
            ->method('createObjectStateFromData')
            ->with($this->equalTo([[]]))
            ->will($this->returnValue(new ObjectState(['id' => 2, 'groupId' => 2])));

        $gatewayMock->expects($this->any())
            ->method('loadObjectStateListData')
            ->with($this->equalTo(2))
            ->will($this->returnValue([[]]));

        $mapperMock->expects($this->any())
            ->method('createObjectStateListFromData')
            ->with($this->equalTo([[]]))
            ->will(
                $this->returnValue(
                    [
                        new ObjectState(['id' => 1, 'groupId' => 2]),
                        new ObjectState(['id' => 2, 'groupId' => 2]),
                        new ObjectState(['id' => 3, 'groupId' => 2]),
                    ]
                )
            );

        $gatewayMock->expects($this->exactly(3))
            ->method('updateObjectStatePriority');

        $gatewayMock->expects($this->at(2))
            ->method('updateObjectStatePriority')
            ->with($this->equalTo(2), $this->equalTo(0));

        $gatewayMock->expects($this->at(3))
            ->method('updateObjectStatePriority')
            ->with($this->equalTo(1), $this->equalTo(1));

        $gatewayMock->expects($this->at(4))
            ->method('updateObjectStatePriority')
            ->with($this->equalTo(3), $this->equalTo(2));

        $handler->setPriority(2, 0);
    }

    public function testDelete()
    {
        $handler = $this->getObjectStateHandler();
        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects($this->once())
            ->method('loadObjectStateData')
            ->with($this->equalTo(1))
            ->will($this->returnValue([[]]));

        $mapperMock->expects($this->once())
            ->method('createObjectStateFromData')
            ->with($this->equalTo([[]]))
            ->will($this->returnValue(new ObjectState(['id' => 1, 'groupId' => 2])));

        $gatewayMock->expects($this->once())
            ->method('deleteObjectState')
            ->with($this->equalTo(1));

        $gatewayMock->expects($this->any())
            ->method('loadObjectStateListData')
            ->with($this->equalTo(2))
            ->will($this->returnValue([[]]));

        $mapperMock->expects($this->any())
            ->method('createObjectStateListFromData')
            ->with($this->equalTo([[]]))
            ->will($this->returnValue([new ObjectState(['id' => 2, 'groupId' => 2])]));

        $gatewayMock->expects($this->once())
            ->method('updateObjectStatePriority')
            ->with($this->equalTo(2), $this->equalTo(0));

        $gatewayMock->expects($this->once())
            ->method('updateObjectStateLinks')
            ->with($this->equalTo(1), $this->equalTo(2));

        $handler->delete(1);
    }

    public function testDeleteThrowsNotFoundException()
    {
        $this->expectException(NotFoundException::class);

        $handler = $this->getObjectStateHandler();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects($this->once())
            ->method('loadObjectStateData')
            ->with($this->equalTo(APIBaseTest::DB_INT_MAX))
            ->will($this->returnValue([]));

        $handler->delete(APIBaseTest::DB_INT_MAX);
    }

    public function testSetContentState()
    {
        $handler = $this->getObjectStateHandler();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects($this->once())
            ->method('setContentState')
            ->with($this->equalTo(42), $this->equalTo(2), $this->equalTo(2));

        $result = $handler->setContentState(42, 2, 2);

        $this->assertTrue($result);
    }

    public function testGetContentState()
    {
        $handler = $this->getObjectStateHandler();
        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects($this->once())
            ->method('loadObjectStateDataForContent')
            ->with($this->equalTo(42), $this->equalTo(2))
            ->will($this->returnValue([[]]));

        $mapperMock->expects($this->once())
            ->method('createObjectStateFromData')
            ->with($this->equalTo([[]]))
            ->will($this->returnValue($this->getObjectStateFixture()));

        $result = $handler->getContentState(42, 2);

        $this->assertInstanceOf(
            ObjectState::class,
            $result
        );
    }

    public function testGetContentCount()
    {
        $handler = $this->getObjectStateHandler();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects($this->once())
            ->method('getContentCount')
            ->with($this->equalTo(1))
            ->will($this->returnValue(185));

        $result = $handler->getContentCount(1);

        $this->assertEquals(185, $result);
    }

    /**
     * Returns an object state.
     *
     * @return \Ibexa\Contracts\Core\Persistence\Content\ObjectState
     */
    protected function getObjectStateFixture()
    {
        return new ObjectState();
    }

    /**
     * Returns an object state group.
     *
     * @return \Ibexa\Contracts\Core\Persistence\Content\ObjectState\Group
     */
    protected function getObjectStateGroupFixture()
    {
        return new Group();
    }

    /**
     * Returns the InputStruct.
     *
     * @return \Ibexa\Contracts\Core\Persistence\Content\ObjectState\InputStruct
     */
    protected function getInputStructFixture()
    {
        return new InputStruct();
    }

    /**
     * Returns the object state handler to test.
     *
     * @return \Ibexa\Core\Persistence\Legacy\Content\ObjectState\Handler
     */
    protected function getObjectStateHandler()
    {
        if (!isset($this->objectStateHandler)) {
            $this->objectStateHandler = new Handler(
                $this->getGatewayMock(),
                $this->getMapperMock()
            );
        }

        return $this->objectStateHandler;
    }

    /**
     * Returns an object state mapper mock.
     *
     * @return \Ibexa\Core\Persistence\Legacy\Content\ObjectState\Mapper
     */
    protected function getMapperMock()
    {
        if (!isset($this->mapperMock)) {
            $this->mapperMock = $this->getMockBuilder(Mapper::class)
                ->setConstructorArgs([$this->getLanguageHandler()])
                ->setMethods([])
                ->getMock();
        }

        return $this->mapperMock;
    }

    /**
     * Returns a mock for the object state gateway.
     *
     * @return \Ibexa\Core\Persistence\Legacy\Content\ObjectState\Gateway
     */
    protected function getGatewayMock()
    {
        if (!isset($this->gatewayMock)) {
            $this->gatewayMock = $this->getMockForAbstractClass(Gateway::class);
        }

        return $this->gatewayMock;
    }
}

class_alias(ObjectStateHandlerTest::class, 'eZ\Publish\Core\Persistence\Legacy\Tests\Content\ObjectState\ObjectStateHandlerTest');

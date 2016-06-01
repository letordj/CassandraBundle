<?php

namespace CassandraBundle\Tests\Units\Cassandra\ORM;

use mageekguy\atoum\test;
use CassandraBundle\Cassandra\ORM\EntityManager as TestedClass;
use CassandraBundle\Cassandra\Connection as CassandraConnection;

class EntityManager extends test
{

    public function testConstruct()
    {
        $this
            ->if($testedClass = new TestedClass($this->getConnection(), $this->getClassMetadataFactoryInterfaceMock(), $this->getLoggerInterfaceMock()))
            ->then
                ->string($testedClass->getKeyspace())
                    ->isEqualTo('test')
        ;
    }

    public function testExecute()
    {
        $this
            ->if($testedClass = new TestedClass($this->getConnection(), $this->getClassMetadataFactoryInterfaceMock(), $this->getLoggerInterfaceMock()))
            ->and($clusterMock = $this->getClusterMock())
            ->and($sessionMock = $this->getSessionMock())
            ->and($clusterMock->getMockController()->connect = $sessionMock)
            ->and($testedClass->getConnection()->setCluster($clusterMock))
            ->and($testedClass->execute($statement = $this->getStatementMock()))
            ->then
                ->mock($clusterMock)
                    ->call('connect')
                        ->once()
                ->mock($sessionMock)
                    ->call('execute')
                        ->withArguments($statement, null)
                        ->once()
        ;
    }

    public function testExecuteRetry()
    {
        $this
            ->if($testedClass = new TestedClass($this->getConnection(), $this->getClassMetadataFactoryInterfaceMock(), $this->getLoggerInterfaceMock()))
            ->and($clusterMock = $this->getClusterMock())
            ->and($sessionMock = $this->getSessionMock(1))
            ->and($clusterMock->getMockController()->connect = $sessionMock)
            ->and($testedClass->getConnection()->setCluster($clusterMock))
            ->and($testedClass->execute($statement = $this->getStatementMock()))
            ->then
                ->mock($clusterMock)
                    ->call('connect')
                        ->twice()
                ->mock($sessionMock)
                    ->call('execute')
                        ->withArguments($statement, null)
                        ->twice()
        ;
    }

    public function testExecuteRetryError()
    {
        $this
            ->if($testedClass = new TestedClass($this->getConnection(), $this->getClassMetadataFactoryInterfaceMock(), $this->getLoggerInterfaceMock()))
            ->and($clusterMock = $this->getClusterMock())
            ->and($sessionMock = $this->getSessionMock(0, true))
            ->and($clusterMock->getMockController()->connect = $sessionMock)
            ->and($testedClass->getConnection()->setCluster($clusterMock))
            ->and($statement = $this->getStatementMock())
            ->then
                ->exception(
                    function() use($testedClass, $statement) {
                        $testedClass->execute($statement);
                    }
                )
                    ->isInstanceOf('\Cassandra\Exception\RuntimeException')
                ->mock($clusterMock)
                    ->call('connect')
                        ->twice()
                ->mock($sessionMock)
                    ->call('execute')
                        ->withArguments($statement, null)
                        ->twice()
        ;
    }

    public function testExecuteAsync()
    {
        $this
            ->if($testedClass = new TestedClass($this->getConnection(), $this->getClassMetadataFactoryInterfaceMock(), $this->getLoggerInterfaceMock()))
            ->and($clusterMock = $this->getClusterMock())
            ->and($sessionMock = $this->getSessionMock())
            ->and($clusterMock->getMockController()->connect = $sessionMock)
            ->and($testedClass->getConnection()->setCluster($clusterMock))
            ->and($testedClass->executeAsync($statement = $this->getStatementMock()))
                ->then
                    ->mock($clusterMock)
                        ->call('connect')
                            ->once()
                    ->mock($sessionMock)
                        ->call('executeAsync')
                            ->withArguments($statement, null)
                            ->once()
        ;
    }

    public function testPrepare()
    {
        $this
            ->if($testedClass = new TestedClass($this->getConnection(), $this->getClassMetadataFactoryInterfaceMock(), $this->getLoggerInterfaceMock()))
            ->and($clusterMock = $this->getClusterMock())
            ->and($sessionMock = $this->getSessionMock())
            ->and($clusterMock->getMockController()->connect = $sessionMock)
            ->and($testedClass->getConnection()->setCluster($clusterMock))
            ->and($testedClass->prepare($cql = 'select * from mytable'))
            ->then
                ->mock($clusterMock)
                    ->call('connect')
                        ->once()
                ->mock($sessionMock)
                    ->call('prepare')
                        ->withArguments($cql, null)
                        ->once()
        ;
    }

    public function testPrepareAsync()
    {
        $this
            ->if($testedClass = new TestedClass($this->getConnection(), $this->getClassMetadataFactoryInterfaceMock(), $this->getLoggerInterfaceMock()))
            ->and($clusterMock = $this->getClusterMock())
            ->and($sessionMock = $this->getSessionMock())
            ->and($clusterMock->getMockController()->connect = $sessionMock)
            ->and($testedClass->getConnection()->setCluster($clusterMock))
            ->and($testedClass->prepareAsync($cql = 'select * from mytable'))
            ->then
                ->mock($clusterMock)
                    ->call('connect')
                        ->once()
                ->mock($sessionMock)
                    ->call('prepareAsync')
                        ->withArguments($cql, null)
                        ->once()
        ;
    }

    public function testEvents()
    {
        $this
            ->if($testedClass = new TestedClass($this->getConnection(), $this->getClassMetadataFactoryInterfaceMock(), $this->getLoggerInterfaceMock()))
            ->and($clusterMock = $this->getClusterMock())
            ->and($sessionMock = $this->getSessionMock())
            ->and($clusterMock->getMockController()->connect = $sessionMock)
            ->and($testedClass->getConnection()->setCluster($clusterMock))
            ->and($testedClass->getConnection()->setEventDispatcher($eventDispatcherMock = new \mock\Symfony\Component\EventDispatcher\EventDispatcherInterface()))
            ->and($testedClass->execute($statement = $this->getStatementMock()))
            ->and($execAsync = $testedClass->executeAsync($statement = $this->getStatementMock()))
            ->and($testedClass->prepare($cql = 'select * from mytable'))
            ->and($prepareAsync = $testedClass->prepareAsync($cql = 'select * from mytable'))
            ->then
                ->mock($eventDispatcherMock)
                    ->call('dispatch')
                        ->exactly(2)
                ->given($this->resetMock($eventDispatcherMock))
                ->if($execAsync->get())
                ->and($prepareAsync->get())
                    ->mock($eventDispatcherMock)
                        ->call('dispatch')
                            ->twice()
        ;


    }

    protected function getConnection()
    {
        return new CassandraConnection([
            'keyspace' => 'test',
            'hosts' => ['127.0.0.1'],
            'user' => '',
            'password' => '',
            'retries' => [ 'sync_requests' => 1 ]
        ]);
    }

    public function getClassMetadataFactoryInterfaceMock()
    {
        $this->getMockGenerator()->shuntParentClassCalls();

        return new \mock\CassandraBundle\Cassandra\ORM\Mapping\ClassMetadataFactoryInterface;
    }

    public function getLoggerInterfaceMock()
    {
        $this->getMockGenerator()->shuntParentClassCalls();

        return new \mock\Psr\Log\LoggerInterface;
    }

    protected function getClusterMock()
    {
        $this->getMockGenerator()->shuntParentClassCalls();

        return new \mock\Cassandra\Cluster;
    }

    public function getSessionMock($retry = 0, $error = false)
    {
        $this->getMockGenerator()->shuntParentClassCalls();

        $session = new \mock\Cassandra\Session();
        $session->getMockController()->executeAsync = new \mock\Cassandra\Future();
        $session->getMockController()->prepareAsync = new \mock\Cassandra\Future();

        $session->getMockController()->execute = function() use (&$retry, $error) {
            if (($error && $retry <= 0) || ($retry > 0)) {
                $retry--;
                throw new \Cassandra\Exception\RuntimeException('runtime error');
            }
        };

        return $session;
    }

    public function getStatementMock()
    {
        $this->getMockGenerator()->shuntParentClassCalls();

        return new \mock\Cassandra\Statement();
    }
}

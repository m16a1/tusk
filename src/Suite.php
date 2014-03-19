<?php

namespace Tusk;

class Suite extends AbstractContext
{
    private $body;

    private $preHooks = array();

    private $postHooks = array();

    private $scope;

    public function __construct(
        $description,
        \Closure $body,
        AbstractContext $parent = null
    ) {
        parent::__construct($description, $parent);
        $this->body = $body;
    }

    public function addPreHook(\Closure $body)
    {
        $this->preHooks[] = $body;
    }

    public function addPostHook(\Closure $body)
    {
        $this->postHooks[] = $body;
    }

    public function executePreHooks(\stdClass $scope)
    {
        if ($this->getParent() !== null) {
            $this->getParent()->executePreHooks($scope);
        }

        foreach ($this->preHooks as $hook) {
            $hook->bindTo($scope, $scope)->__invoke();
        }
    }

    public function executePostHooks(\stdClass $scope)
    {
        foreach ($this->postHooks as $hook) {
            $hook->bindTo($scope, $scope)->__invoke();
        }

        if ($this->getParent() !== null) {
            $this->getParent()->executePostHooks($scope);
        }
    }

    public function getScope()
    {
        return $this->scope;
    }

    public function execute($skip = false)
    {
        if ($this->getParent() !== null) {
            $this->scope = clone $this->getParent()->getScope();
        } else {
            $this->scope = new \stdClass();
        }

        $this->body->bindTo($this->scope, $this->scope)->__invoke();
    }
}

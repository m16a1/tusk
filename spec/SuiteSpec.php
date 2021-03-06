<?php

use Mockery as m;
use Tusk\Suite;

describe('Suite', function() {
    afterEach(function() {
        m::close();
    });

    describe('hook methods', function() {
        beforeEach(function() {
            $this->scope = new \stdClass();
            $this->scope->called = [];
        });

        describe('executePreHooks()', function() {
            it('should execute added pre-hooks using the given scope', function() {
                $suite = new Suite('ignore', function() {});

                for ($i = 1; $i <= 3; ++$i) {
                    $suite->addPreHook(function() use ($i) {
                        $this->called[] = $i;
                    });
                }

                $suite->executePreHooks($this->scope);

                expect($this->scope->called)->toBe([1, 2, 3]);
            });

            it('should execute hooks from parent suite *before* executing its own hooks, if parent is set', function() {
                $parent = m::mock('Tusk\Suite');

                $parent
                    ->shouldReceive('executePreHooks')
                    ->with($this->scope)
                    ->andReturnUsing(function() {
                        $this->scope->called[] = 'parent';
                    })
                ;

                $suite = new Suite('ignore', function() {}, $parent);

                $suite->addPreHook(function() {
                    $this->called[] = 'self';
                });

                $suite->executePreHooks($this->scope);

                expect($this->scope->called)->toBe(['parent', 'self']);
            });
        });

        describe('executePostHooks()', function() {
            it('should execute added post-hooks using the given scope', function() {
                $suite = new Suite('ignore', function() {});

                for ($i = 1; $i <= 3; ++$i) {
                    $suite->addPostHook(function() use ($i) {
                        $this->called[] = $i;
                    });
                }

                $suite->executePostHooks($this->scope);

                expect($this->scope->called)->toBe([1, 2, 3]);
            });

            it('should execute hooks from parent suite *after* executing its own hooks, if parent is set', function() {
                $parent = m::mock('Tusk\Suite');

                $parent
                    ->shouldReceive('executePostHooks')
                    ->with($this->scope)
                    ->andReturnUsing(function() {
                        $this->scope->called[] = 'parent';
                    })
                ;

                $suite = new Suite('ignore', function() {}, $parent);

                $suite->addPostHook(function() {
                    $this->called[] = 'self';
                });

                $suite->executePostHooks($this->scope);

                expect($this->scope->called)->toBe(['self', 'parent']);
            });
        });
    });

    describe('execute()', function() {
        it('should execute body using a clone of the parent scope, if parent is set', function() {
            $scope = new \stdClass();
            $scope->foo = 'bar';

            $parent = m::mock('Tusk\Suite', ['getScope' => $scope]);

            $bodyCalled = false;

            $body = function() use (&$bodyCalled, $scope) {
                $bodyCalled = true;
                expect($this)->toEqual($scope);
                expect($this)->notToBe($scope);
            };

            $suite = new Suite('ignore', $body, $parent);
            $suite->setUp();

            expect($bodyCalled)->toBe(true);

            // Test 'getScope' for good measure
            expect($suite->getScope())->toEqual($scope);
            expect($suite->getScope())->notToBe($scope);
        });

        it('should execute body using a new scope, if parent is not set', function() {
            $bodyCalled = false;

            $body = function() use (&$bodyCalled) {
                $bodyCalled = true;
                expect($this)->toBeInstanceOf('stdClass');
                expect($this)->toEqual(new \stdClass());
            };

            $suite = new Suite('ignore', $body);
            $suite->setUp();

            expect($bodyCalled)->toBe(true);

            // Test 'getScope' for good measure
            expect($suite->getScope())->toBeInstanceOf('stdClass');
            expect($suite->getScope())->toEqual(new \stdClass());
        });
    });
});

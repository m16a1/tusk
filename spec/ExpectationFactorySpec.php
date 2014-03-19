<?php

use Mockery as m;
use Tusk\Expectation;
use Tusk\ExpectationFactory;

describe('ExpectationFactory', function() {
    beforeEach(function() {
        $this->prettyPrinter = m::mock('Tusk\PrettyPrinter');

        $this->factory = new ExpectationFactory($this->prettyPrinter);
    });

    afterEach(function() {
        m::close();
    });

    describe('createExpectation()', function() {
        it('should create an expectation with the comparators added in addComparator()', function() {
            $value = 'foo';

            $comparators = [
                'comparator1' => m::mock('Tusk\Comparator'),
                'comparator2' => m::mock('Tusk\Comparator')
            ];

            $context = m::mock('Tusk\AbstractContext');

            foreach ($comparators as $key => $value) {
                $this->factory->addComparator($key, $value);
            }

            $expectation = $this->factory->createExpectation($value);

            expect($expectation)->toBeInstanceOf('Tusk\Expectation');

            expect($expectation)->toEqual(
                new Expectation(
                    $value,
                    $comparators,
                    $this->prettyPrinter
                )
            );
        });
    });
});

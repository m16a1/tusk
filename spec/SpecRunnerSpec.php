<?php

use Mockery as m;
use Tusk\SpecRunner;

describe('SpecRunner', function() {
    beforeEach(function() {
        $this->progressOutput = m::mock('Tusk\ProgressOutput');
        $this->specRunner = new SpecRunner($this->progressOutput);
    });

    afterEach(function() {
        m::close();
    });

    describe('run()', function() {
        it('should run the previously added specs', function() {
            for ($i = 0; $i < 3; ++$i) {
                $spec = m::mock('Tusk\Spec', ['isSkipped' => false]);
                $spec->shouldReceive('run')->once();
                $this->specRunner->add($spec);
            }

            $this->progressOutput->shouldReceive('setTotalSpecs')->with(3)->once();
            $this->progressOutput->shouldReceive('pass')->times(3);
            $this->progressOutput->shouldReceive('fail')->never();
            $this->progressOutput->shouldReceive('skip')->never();

            $this->specRunner->run();

            expect($this->specRunner->getFailCount())->toBe(0);
            expect($this->specRunner->getFailedSpecs())->toBe([]);
            expect($this->specRunner->getSkipCount())->toBe(0);
        });

        it('should catch exceptions and fail the spec when they occur', function() {
            for ($i = 0; $i < 3; ++$i) {
                if ($i === 1) {
                    $spec = m::mock('Tusk\Spec', [
                        'getDescription' => 'failing spec',
                        'isSkipped' => false
                    ]);

                    $spec
                        ->shouldReceive('run')
                        ->once()
                        ->andThrow(new \Exception('error'))
                    ;

                } else {
                    $spec = m::mock('Tusk\Spec', [
                        'getDescription' => 'passing spec',
                        'isSkipped' => false
                    ]);

                    $spec = m::mock('Tusk\Spec', ['isSkipped' => false]);
                    $spec->shouldReceive('run')->once();
                }

                $this->specRunner->add($spec);
            }

            $this->progressOutput->shouldReceive('setTotalSpecs')->with(3)->once();
            $this->progressOutput->shouldReceive('pass')->twice();
            $this->progressOutput->shouldReceive('fail')->once();
            $this->progressOutput->shouldReceive('skip')->never();

            $this->specRunner->run();

            expect($this->specRunner->getFailCount())->toBe(1);
            expect($this->specRunner->getFailedSpecs())->toBe(['failing spec' => 'error']);
            expect($this->specRunner->getSkipCount())->toBe(0);
        });

        it('should not run any specs where "isSkipped" returns true', function() {
            for ($i = 0; $i < 3; ++$i) {
                if ($i === 1) {
                    $spec = m::mock('Tusk\Spec', ['isSkipped' => true]);
                    $spec->shouldReceive('run')->never();

                } else {
                    $spec = m::mock('Tusk\Spec', ['isSkipped' => false]);
                    $spec->shouldReceive('run')->once();
                }

                $this->specRunner->add($spec);
            }

            $this->progressOutput->shouldReceive('setTotalSpecs')->with(3)->once();
            $this->progressOutput->shouldReceive('pass')->twice();
            $this->progressOutput->shouldReceive('fail')->never();
            $this->progressOutput->shouldReceive('skip')->once();

            $this->specRunner->run();

            expect($this->specRunner->getFailCount())->toBe(0);
            expect($this->specRunner->getFailedSpecs())->toBe([]);
            expect($this->specRunner->getSkipCount())->toBe(1);
        });
    });
});
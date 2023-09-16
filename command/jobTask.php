<?php

declare(strict_types=1);

namespace tete;

use Closure;
use Exception;
use SyncSemaphore;
use Throwable;

class Task
{
    /**
     * @param callable|Closure|string $callable
     * @param array<mixed> $arguments
     */
    public function __construct(
        protected readonly mixed $callable,
        protected readonly array $arguments,
        protected readonly bool  $retry = false,
        protected readonly int   $retryCount = 3,
        protected readonly int   $retryInterval = 3000,
    ) {
    }

    /**
     * @return callable|Closure|string
     */
    public function getCallable(): callable|Closure|string
    {
        return $this->callable;
    }

    /**
     * @return array<mixed>
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @return bool
     */
    public function isRetry(): bool
    {
        return $this->retry;
    }

    /**
     * @return int
     */
    public function getRetryCount(): int
    {
        return $this->retryCount;
    }

    /**
     * @return int
     */
    public function getRetryInterval(): int
    {
        return $this->retryInterval;
    }
}

class TasksQueue
{
    protected const SEMAPHORE_NAME = 'maxTask';

    /**
     * @var array<Task>
     */
    protected array $taskStore = [];

    protected SyncSemaphore $maxTaskSemaphore;

    protected int $maxTasks;

    /**
     * @var array<int>
     */
    protected array $currentJobs = [];

    public function __construct(int $maxTasks = 2)
    {
        $this->setMaxTasks($maxTasks);
        pcntl_signal(SIGCHLD, function (int $sig) {
            $pid = pcntl_waitpid(-1, $status, WNOHANG);
            if ($pid) {
                echo "Pid #$pid exited with status $status" . PHP_EOL;
                unset($this->currentJobs[$pid]);
            }
        });
    }

    /**
     * @throws Exception
     */
    public function run(): void
    {
        $this->maxTaskSemaphore ??= $this->createSemaphore();

        echo "Start" . PHP_EOL;
        foreach ($this->taskStore as $id => $task) {
            echo "Try get lock for #$id" . PHP_EOL;
            $this->maxTaskSemaphore->lock();
            echo "Get lock for #$id" . PHP_EOL;

            pcntl_signal_dispatch();
            $pid = pcntl_fork();
            if ($pid === 0) {
                $pid = getmypid();
                echo "#$id ($pid) Execute callable" . PHP_EOL;

                $continue = false;
                $status = 0;
                do {
                    try {
                        $task->getCallable()(...$task->getArguments());
                    } catch (Throwable $e) {
                        echo "#$id ($pid) Catch error - {$e->getMessage()}" . PHP_EOL;
                        if ($task->isRetry()) {
                            $cntRetry ??= $task->getRetryCount();
                            if ($cntRetry != 0) {
                                $cntRetry--;
                                usleep($task->getRetryInterval());
                                echo "#$id ($pid) Retry" . PHP_EOL;
                                continue;
                            }
                        }
                        echo "#$id ($pid) Continue with error" . PHP_EOL;
                        $status = 1;
                    }
                    $continue = true;
                } while ($continue === false);

                echo "#$id ($pid) Execute done" . PHP_EOL;
                echo "#$id ($pid) Unlock" . PHP_EOL;

                $this->maxTaskSemaphore->unlock();

                exit($status);
            }
            $this->currentJobs[$pid] = $pid;
        }

        echo "Pid wait" . PHP_EOL;
        foreach ($this->currentJobs as $jobPid) {
            $pid = pcntl_waitpid($jobPid, $status);
            echo "(Last) Pid #$pid exited with status $status" . PHP_EOL;
        }
    }

    /**
     * @param callable|Closure|string $callable
     * @param bool $retry
     * @param int $retryCount
     * @param int $retryInterval
     * @param mixed ...$arguments
     * @return void
     */
    public function addTask(
        callable|Closure|string $callable,
        bool                    $retry = false,
        int                     $retryCount = 3,
        int                     $retryInterval = 3000,
        mixed                   ...$arguments
    ): void {
        $this->taskStore[] = new Task($callable, array_values($arguments), $retry, $retryCount, $retryInterval);
    }

    /**
     * @return int
     */
    public function getMaxTasks(): int
    {
        return $this->maxTasks;
    }

    /**
     * @param int $maxTasks
     */
    public function setMaxTasks(int $maxTasks): void
    {
        $this->maxTasks = $maxTasks;
    }

    /**
     * @throws Exception
     */
    protected function createSemaphore(): SyncSemaphore
    {
        return new SyncSemaphore(
            name: self::SEMAPHORE_NAME . $this->getMaxTasks(),
            initialval: $this->getMaxTasks(),
            autounlock: 0,
        );
    }
}

function sleepyPrinter(int $s): void
{
    $pid = getmypid();
    sleep($s);
    echo "Pid #$pid - I slept for $s seconds!" . PHP_EOL;
}

$taskQueue = new TasksQueue();

$taskQueue->addTask(callable: 'tete\sleepyPrinter', arguments: 1);
$taskQueue->addTask(callable: 'tete\sleepyPrinter'(...), arguments: 1);
$taskQueue->addTask(callable: function (int $s) {
    if (rand(0, 1)) {
        throw new Exception('error');
    }
    sleepyPrinter($s);
}, retry: true, retryCount: 5, retryInterval: 10000, arguments: 1);

for ($i = 0; $i < 5; $i++) {
    $taskQueue->addTask(callable: 'tete\sleepyPrinter', arguments: 1);
}

$taskQueue->run();

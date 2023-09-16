<?php

declare(strict_types=1);

/**
 * @param int $key
 * @return SysvSharedMemory
 *
 * @throws RuntimeException
 */
function shmGet(int $key): SysvSharedMemory
{
    $shm = shm_attach($key);

    if ($shm === false) {
        throw new RuntimeException("Shared memory get error");
    }

    return $shm;
}

/**
 * @param int $key
 * @param int $size
 * @param int $permissions
 * @return SysvSharedMemory
 *
 * @throws RuntimeException
 */
function shmCreate(int $key, int $size = 5000, int $permissions = 0644): SysvSharedMemory
{
    $shm = shm_attach($key, $size, $permissions);

    if ($shm === false) {
        throw new RuntimeException("Shared memory create error");
    }

    return $shm;
}

/**
 * @param SysvSharedMemory $sharedMemory
 * @param int $valKey
 * @param mixed $data
 * @return void
 *
 * @throws RuntimeException
 */
function shmPut(SysvSharedMemory $sharedMemory, int $valKey, mixed $data): void
{
    $res = shm_put_var($sharedMemory, $valKey, $data);

    if ($res === false) {
        throw new RuntimeException("Shared memory put error");
    }
}

/**
 * @param SysvSharedMemory $sharedMemory
 * @return void
 *
 * @throws RuntimeException
 */
function shmDetach(SysvSharedMemory $sharedMemory): void
{
    $res = shm_detach($sharedMemory);

    if ($res === false) {
        throw new RuntimeException("Shared memory detach error");
    }
}

/**
 * @param SysvSharedMemory $sharedMemory
 * @return void
 *
 * @throws RuntimeException
 */
function shmDelete(SysvSharedMemory $sharedMemory): void
{
    $res = shm_remove($sharedMemory);

    if ($res === false) {
        throw new RuntimeException("Shared memory delete error");
    }
}


/**
 * @param int $key
 * @return SysvSemaphore
 *
 * @throws RuntimeException
 */
function semGet(int $key): SysvSemaphore
{
    $sem = sem_get($key);

    if ($sem === false) {
        throw new RuntimeException("Semaphore get error");
    }

    return $sem;
}

/**
 * @param int $key
 * @param int $maxAcquire
 * @param int $permissions
 * @param bool $autoRelease
 * @return SysvSemaphore
 *
 * @throws RuntimeException
 */
function semCreate(int $key, int $maxAcquire = 1, int $permissions = 0644, bool $autoRelease = false): SysvSemaphore
{
    $sem = sem_get($key, $maxAcquire, $permissions, $autoRelease);

    if ($sem === false) {
        throw new RuntimeException("Semaphore create error");
    }

    return $sem;
}

/**
 * @param SysvSemaphore $semaphore
 * @return void
 *
 * @throws RuntimeException
 */
function semRemove(SysvSemaphore $semaphore): void
{
    $res = sem_remove($semaphore);

    if ($res === false) {
        throw new RuntimeException("Semaphore remove error");
    }
}

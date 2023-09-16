<?php

/**
 * @param int $key
 * @param int $len
 * @return Shmop
 *
 * @throws RuntimeException
 */
function reserveShm(int $key, int $len): Shmop
{
    $shm = shmop_open(
        $key,
        'c',
        0644,
        $len
    );

    if (!$shm) {
        throw new RuntimeException('Shared memory create error');
    }

    return $shm;
}

/**
 * @param int $key
 * @return Shmop
 *
 * @throws RuntimeException
 */
function getShmForRead(int $key): Shmop
{
    $shm = shmop_open(
        $key,
        'a',
        0,
        0
    );

    if (!$shm) {
        throw new RuntimeException('Get shared memory for read error');
    }

    return $shm;
}

/**
 * @param Shmop $shm
 * @param int $offset
 * @param int|null $size
 * @return string
 *
 * @throws RuntimeException
 */
function readShm(Shmop $shm, int $offset = 0, int $size = null): string
{
    $data = shmop_read($shm, $offset, $size ?? shmop_size($shm));

    if ($data === false) {
        throw new RuntimeException('Shared memory read error');
    }

    return $data;
}

/**
 * @param Shmop $shm
 * @param string $data
 * @param int $offset
 * @return int
 *
 * @throws RuntimeException
 */
function writeShm(Shmop $shm, string $data, int $offset = 0): int
{
    try {
        return shmop_write($shm, $data, $offset);
    } catch (ValueError $e) {
        throw new RuntimeException($e->getMessage());
    }
}

/**
 * @param Shmop $shm
 * @return void
 *
 * @throws RuntimeException
 */
function deleteShm(Shmop $shm): void
{
    if (false === shmop_delete($shm)) {
        throw new RuntimeException('Shared memory delete error');
    }
}

<?php

namespace Akseonov\Php2\UnitTests\Blog\Repositories\AuthRepositories;
use Akseonov\Php2\Blog\AuthToken;
use Akseonov\Php2\Blog\Repositories\AuthTokensRepository\SqliteAuthTokensRepository;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\AuthTokenNotFoundException;
use Akseonov\Php2\Exceptions\AuthTokensRepositoryException;
use Akseonov\Php2\Exceptions\InvalidArgumentException;
use Akseonov\Php2\UnitTests\DummyLogger;
use DateTimeImmutable;
use Monolog\Test\TestCase;
use PDO;
use PDOException;
use PDOStatement;

class SqliteAuthTokensRepositoryTest extends TestCase
{
    /**
     * @throws AuthTokensRepositoryException
     */
    public function testItThrowsAnExceptionWhenTokenNotFound(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementStub = $this->createStub(PDOStatement::class);

        $statementStub->method('fetch')->willReturn(false);
        $connectionStub->method('prepare')->willReturn($statementStub);

        $repository = new SqliteAuthTokensRepository($connectionStub, new DummyLogger());

        $this->expectException(AuthTokenNotFoundException::class);
        $this->expectExceptionMessage('Cannot find token: 4f6216269ad31d93d0a2f1fca264adabaf6ffc2cbeda903a4879fdfb5a53e9d59ad7141b979e0d52');

        $repository->get(
            '4f6216269ad31d93d0a2f1fca264adabaf6ffc2cbeda903a4879fdfb5a53e9d59ad7141b979e0d52',
        );
    }

    /**
     * @throws AuthTokensRepositoryException
     * @throws AuthTokenNotFoundException
     */
    public function testItReturnAuthTokenObjectByToken(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);

        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->with([
                '4f6216269ad31d93d0a2f1fca264adabaf6ffc2cbeda903a4879fdfb5a53e9d59ad7141b979e0d52',
            ]);

        $connectionStub->method('prepare')->willReturn($statementMock);

        $statementMock
            ->expects($this->once())
            ->method('fetch')
            ->willReturn([
                'token' => '4f6216269ad31d93d0a2f1fca264adabaf6ffc2cbeda903a4879fdfb5a53e9d59ad7141b979e0d52',
                'user_uuid' => '3a6ec3a6-f25b-438a-93d4-8392f620a702',
                'expires_on' => '2022-08-29T13:14:10+00:00',
            ]);

        $repository = new SqliteAuthTokensRepository($connectionStub, new DummyLogger());

        $result = $repository->get('4f6216269ad31d93d0a2f1fca264adabaf6ffc2cbeda903a4879fdfb5a53e9d59ad7141b979e0d52');

        $this->assertEquals(new AuthToken(
            '4f6216269ad31d93d0a2f1fca264adabaf6ffc2cbeda903a4879fdfb5a53e9d59ad7141b979e0d52',
            new UUID('3a6ec3a6-f25b-438a-93d4-8392f620a702'),
            new DateTimeImmutable('2022-08-29T13:14:10+00:00'),
        ), $result);
    }

    public function testItReturnPDOException(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);

        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->with([
                '4f6216269ad31d93d0a2f1fca264adabaf6ffc2cbeda903a4879fdfb5a53e9d59ad7141b979e0d52',
            ])->willThrowException(
                new PDOException(
                    'SQLSTATE[HY000] General error: 1 no such table: token',
                    '404',
                )
            );

        $connectionStub->method('prepare')->willReturn($statementMock);

        $repository = new SqliteAuthTokensRepository($connectionStub, new DummyLogger());

        $this->expectException(PDOException::class);
        $this->expectException(AuthTokensRepositoryException::class);
        $this->expectExceptionMessage('SQLSTATE[HY000] General error: 1 no such table: token');

        $repository->get('4f6216269ad31d93d0a2f1fca264adabaf6ffc2cbeda903a4879fdfb5a53e9d59ad7141b979e0d52');
    }

    public function testItThrowInvalidArgumentException(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);

        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->with([
                '4f6216269ad31d93d0a2f1fca264adabaf6ffc2cbeda903a4879fdfb5a53e9d59ad7141b979e0d52',
            ]);

        $connectionStub->method('prepare')->willReturn($statementMock);

        $statementMock
            ->expects($this->once())
            ->method('fetch')
            ->willReturn([
                'token' => '4f6216269ad31d93d0a2f1fca264adabaf6ffc2cbeda903a4879fdfb5a53e9d59ad7141b979e0d52',
                'user_uuid' => '3a6ec3a6',
                'expires_on' => '2022-08-29T13:14:10+00:00',
            ]);

        $repository = new SqliteAuthTokensRepository($connectionStub, new DummyLogger());

        $this->expectException(AuthTokensRepositoryException::class);
        $this->expectExceptionMessage('Malformed UUID: 3a6ec3a6');

        $repository->get('4f6216269ad31d93d0a2f1fca264adabaf6ffc2cbeda903a4879fdfb5a53e9d59ad7141b979e0d52');
    }

    /**
     * @throws AuthTokensRepositoryException
     */
    public function testItThrowExceptionWhenSave(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);

        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->willThrowException(
                new PDOException(
                    'SQLSTATE[HY000] General error: 1 no such table: token',
                    '404',
                )
            );

        $connectionStub->method('prepare')->willReturn($statementMock);

        $this->expectException(AuthTokensRepositoryException::class);
        $this->expectExceptionMessage('SQLSTATE[HY000] General error: 1 no such table: token');

        $repository = new SqliteAuthTokensRepository($connectionStub, new DummyLogger());

        $repository->save(
            new AuthToken(
                '4f6216269ad31d93d0a2f1fca264adabaf6ffc2cbeda903a4879fdfb5a53e9d59ad7141b979e0d52',
                new UUID('3a6ec3a6-f25b-438a-93d4-8392f620a702'),
                new DateTimeImmutable('2022-08-29T13:14:10+00:00'),
            )
        );
    }

    /**
     * @throws AuthTokensRepositoryException
     */
    public function testItSavesAuthTokenToDatabase(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);

        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->with([
                ':token' => '4f6216269ad31d93d0a2f1fca264adabaf6ffc2cbeda903a4879fdfb5a53e9d59ad7141b979e0d52',
                ':user_uuid' => '3a6ec3a6-f25b-438a-93d4-8392f620a702',
                ':expires_on' => '2022-08-29T13:14:10+00:00',
            ]);

        $connectionStub->method('prepare')->willReturn($statementMock);

        $repository = new SqliteAuthTokensRepository($connectionStub, new DummyLogger());

        $repository->save(
            new AuthToken(
                '4f6216269ad31d93d0a2f1fca264adabaf6ffc2cbeda903a4879fdfb5a53e9d59ad7141b979e0d52',
                new UUID('3a6ec3a6-f25b-438a-93d4-8392f620a702'),
                new DateTimeImmutable('2022-08-29T13:14:10+00:00'),
            )
        );
    }
}
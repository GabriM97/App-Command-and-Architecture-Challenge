<?php

namespace Tests\Repositories;

use Mockery;
use Tests\TestCase;
use App\Enums\Query;
use Mockery\MockInterface;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class UserRepositoryTest extends TestCase
{
    /**
     * @var $repository the user repository instance
     */
    protected UserRepository $repository;

        /**
     * @var $builder a mock for the User model
     */
    protected Builder|MockInterface $builder;

    /**
     * @var $collection a mock for the Users Collections
     */
    protected Collection|MockInterface $collection;

        /**
     * @var array $arguments the function arguments
     */
    protected array $arguments = [];

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setupArguments();

        $this->repository = $this->app->make(UserRepository::class);

        $this->builder = Mockery::mock(Builder::class);

        $this->collection = Mockery::mock(Collection::class);
        
    }

    /**
     * @return void
     */
    protected function setupArguments(): void
    {
        $this->arguments = [
            'columns' => ['*'],
            'trashed' => Query::Exclude,
            'admin' => Query::Include,
            'active' => Query::Include,
            'sortBy' => 'email',
        ];
    }

    /**
     * Test Get Banned Users using the default arguments
     * 
     * @return void
     */
    public function testGetBannedUsersWithDefaultParameters()
    {
        $this->builder->shouldNotReceive(
            ['withTrashed', 'onlyTrashed', 'whereDoesntHave', 'whereHas', 'whereNotNull']
        );

        $this->collection->shouldReceive('makeVisible')->once()->with('banned_at')->andReturnSelf();

        $this->builder->shouldReceive('orderBy')->once()->with($this->arguments['sortBy'])->andReturnSelf();
        $this->builder->shouldReceive('get')->once()->with($this->arguments['columns'])->andReturn($this->collection);

        $this->userAlias->shouldReceive('whereNotNull')->with('banned_at')->once()
            ->andReturn($this->builder);

        $result = $this->repository->getBannedUsers();

        $this->assertSame($this->collection, $result);
    }

    /**
     * Test Get Banned Users with trashed include parameter
     * 
     * @return void
     */
    public function testGetBannedUsersWithTrashedIncludeParameter()
    {
        $this->builder->shouldNotReceive(
            ['onlyTrashed', 'whereDoesntHave', 'whereHas', 'whereNotNull']
        );

        $this->builder->shouldReceive('withTrashed')->once()->withNoArgs();

        $this->collection->shouldReceive('makeVisible')->once()->with('banned_at')->andReturnSelf();

        $this->builder->shouldReceive('orderBy')->once()->with($this->arguments['sortBy'])->andReturnSelf();
        $this->builder->shouldReceive('get')->once()->with($this->arguments['columns'])->andReturn($this->collection);

        $this->userAlias->shouldReceive('whereNotNull')->with('banned_at')->once()
            ->andReturn($this->builder);

        $this->arguments['trashed'] = Query::Include;
        $result = $this->repository->getBannedUsers(...$this->arguments);

        $this->assertSame($this->collection, $result);
    }

    /**
     * Test Get Banned Users with trashed only parameter
     * 
     * @return void
     */
    public function testGetBannedUsersWithTrashedOnlyParameter()
    {
        $this->builder->shouldNotReceive(
            ['withTrashed', 'whereDoesntHave', 'whereHas', 'whereNotNull']
        );

        $this->builder->shouldReceive('onlyTrashed')->once()->withNoArgs();

        $this->collection->shouldReceive('makeVisible')->once()->with('banned_at')->andReturnSelf();

        $this->builder->shouldReceive('orderBy')->once()->with($this->arguments['sortBy'])->andReturnSelf();
        $this->builder->shouldReceive('get')->once()->with($this->arguments['columns'])->andReturn($this->collection);

        $this->userAlias->shouldReceive('whereNotNull')->with('banned_at')->once()
            ->andReturn($this->builder);

        $this->arguments['trashed'] = Query::Only;
        $result = $this->repository->getBannedUsers(...$this->arguments);

        $this->assertSame($this->collection, $result);
    }

    /**
     * Test Get Banned Users with admin exclude parameter
     * 
     * @return void
     */
    public function testGetBannedUsersWithAdminExcludeParameter()
    {
        $this->builder->shouldNotReceive(
            ['withTrashed', 'onlyTrashed', 'whereHas', 'whereNotNull']
        );

        $this->builder->shouldReceive('whereDoesntHave')->once()->with('roles', \Closure::class);

        $this->collection->shouldReceive('makeVisible')->once()->with('banned_at')->andReturnSelf();

        $this->builder->shouldReceive('orderBy')->once()->with($this->arguments['sortBy'])->andReturnSelf();
        $this->builder->shouldReceive('get')->once()->with($this->arguments['columns'])->andReturn($this->collection);

        $this->userAlias->shouldReceive('whereNotNull')->with('banned_at')->once()
            ->andReturn($this->builder);

        $this->arguments['admin'] = Query::Exclude;
        $result = $this->repository->getBannedUsers(...$this->arguments);

        $this->assertSame($this->collection, $result);
    }

     /**
     * Test Get Banned Users with admin only parameter
     * 
     * @return void
     */
    public function testGetBannedUsersWithAdminOnlyParameter()
    {
        $this->builder->shouldNotReceive(
            ['withTrashed', 'onlyTrashed', 'whereDoesntHave', 'whereNotNull']
        );

        $this->builder->shouldReceive('whereHas')->once()->with('roles', \Closure::class);

        $this->collection->shouldReceive('makeVisible')->once()->with('banned_at')->andReturnSelf();

        $this->builder->shouldReceive('orderBy')->once()->with($this->arguments['sortBy'])->andReturnSelf();
        $this->builder->shouldReceive('get')->once()->with($this->arguments['columns'])->andReturn($this->collection);

        $this->userAlias->shouldReceive('whereNotNull')->with('banned_at')->once()
            ->andReturn($this->builder);

        $this->arguments['admin'] = Query::Only;
        $result = $this->repository->getBannedUsers(...$this->arguments);

        $this->assertSame($this->collection, $result);
    }

    /**
     * Test Get Banned Users with active only parameter
     * 
     * @return void
     */
    public function testGetBannedUsersWithActiveOnlyParameter()
    {
        $this->builder->shouldNotReceive(
            ['withTrashed', 'onlyTrashed', 'whereDoesntHave', 'whereHas']
        );

        $this->builder->shouldReceive('whereNotNull')->once()->with('activated_at');

        $this->collection->shouldReceive('makeVisible')->once()->with('banned_at')->andReturnSelf();

        $this->builder->shouldReceive('orderBy')->once()->with($this->arguments['sortBy'])->andReturnSelf();
        $this->builder->shouldReceive('get')->once()->with($this->arguments['columns'])->andReturn($this->collection);

        $this->userAlias->shouldReceive('whereNotNull')->with('banned_at')->once()
            ->andReturn($this->builder);

        $this->arguments['active'] = Query::Only;
        $result = $this->repository->getBannedUsers(...$this->arguments);

        $this->assertSame($this->collection, $result);
    }
}
<?php

namespace Tests\Services;

use Tests\TestCase;
use App\Enums\Query;
use App\Exceptions\IncompatibleOptionsException;
use App\Services\UsersCommandsOptionsResolver;

class UsersCommandsOptionsResolverTest extends TestCase
{
    /**
     * @var $resolver the user commands options resolver instance
     */
    protected UsersCommandsOptionsResolver $resolver;

    /**
     * @var $options the default options to resolve
     */
    protected array $options;

        /**
     * @var $resolvedOptions the default resolved options
     */
    protected array $resolvedOptions;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->options = [
            'with-trashed' => false,
            'trashed-only' => false,
            'no-admin' => false,
            'admin-only' => false,
            'active-users-only' => false,
            'sort-by' => 'email',
        ];

        $this->resolvedOptions = [
            'trashed' => Query::Exclude,
            'admin' => Query::Include,
            'active' => Query::Include,
            'sort-by' => 'email'
        ];

        $this->resolver = $this->app->make(UsersCommandsOptionsResolver::class);
    }

    /**
     * Test Resolve returns resolved options when passing the default options
     * 
     * @return void
     */
    public function testResolveReturnsResolvedOptionsWithDefaultOptions()
    {
        $resolved = $this->resolver->resolve($this->options);

        $this->assertSame($this->resolvedOptions, $resolved);
    }

    /**
     * Test Resolve throws exception when passing incompatible options (trashed)
     * 
     * @return void
     */
    public function testResolveThrowsIncompatibleOptionsExceptionWhenPassingIncompatibleTrashedOptions()
    {
        $this->expectException(IncompatibleOptionsException::class);

        $this->options['with-trashed'] = true;
        $this->options['trashed-only'] = true;
        
        $this->resolver->resolve($this->options);
    }

    /**
     * Test Resolve throws exception when passing incompatible options (admin)
     * 
     * @return void
     */
    public function testResolveThrowsIncompatibleOptionsExceptionWhenPassingIncompatibleAdminOptions()
    {
        $this->expectException(IncompatibleOptionsException::class);

        $this->options['no-admin'] = true;
        $this->options['admin-only'] = true;
        
        $this->resolver->resolve($this->options);
    }

    /**
     * Test Resolve returns resolved options when passing with-trashed option
     * 
     * @return void
     */
    public function testResolveReturnsResolvedOptionsWhenPassingWithTrashedOption()
    {
        $this->options['with-trashed'] = true;

        $resolved = $this->resolver->resolve($this->options);

        $this->resolvedOptions['trashed'] = Query::Include;

        $this->assertSame($this->resolvedOptions, $resolved);
    }

    /**
     * Test Resolve returns resolved options when passing trashed-only option
     * 
     * @return void
     */
    public function testResolveReturnsResolvedOptionsWhenPassingTrashedOnlyOption()
    {
        $this->options['trashed-only'] = true;

        $resolved = $this->resolver->resolve($this->options);

        $this->resolvedOptions['trashed'] = Query::Only;

        $this->assertSame($this->resolvedOptions, $resolved);
    }

    /**
     * Test Resolve returns resolved options when passing no-admin option
     * 
     * @return void
     */
    public function testResolveReturnsResolvedOptionsWhenPassingNoAdminOption()
    {
        $this->options['no-admin'] = true;

        $resolved = $this->resolver->resolve($this->options);

        $this->resolvedOptions['admin'] = Query::Exclude;

        $this->assertSame($this->resolvedOptions, $resolved);
    }

    /**
     * Test Resolve returns resolved options when passing admin-only option
     * 
     * @return void
     */
    public function testResolveReturnsResolvedOptionsWhenPassingAdminOnlyOption()
    {
        $this->options['admin-only'] = true;

        $resolved = $this->resolver->resolve($this->options);

        $this->resolvedOptions['admin'] = Query::Only;

        $this->assertSame($this->resolvedOptions, $resolved);
    }

    /**
     * Test Resolve returns resolved options when passing active-users-only option
     * 
     * @return void
     */
    public function testResolveReturnsResolvedOptionsWhenPassingActiveUsersOnlyOption()
    {
        $this->options['active-users-only'] = true;

        $resolved = $this->resolver->resolve($this->options);

        $this->resolvedOptions['active'] = Query::Only;

        $this->assertSame($this->resolvedOptions, $resolved);
    }

    /**
     * Test Resolve returns resolved options when passing mixed options
     * 
     * @return void
     */
    public function testResolveReturnsResolvedOptionsWhenPassingMixedOptions()
    {
        $this->options['active-users-only'] = true;
        $this->options['no-admin'] = true;
        $this->options['with-trashed'] = true;        

        $resolved = $this->resolver->resolve($this->options);

        $this->resolvedOptions['active'] = Query::Only;
        $this->resolvedOptions['admin'] = Query::Exclude;
        $this->resolvedOptions['trashed'] = Query::Include;

        $this->assertSame($this->resolvedOptions, $resolved);
    }
}
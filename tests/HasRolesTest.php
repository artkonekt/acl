<?php

declare(strict_types=1);

namespace Konekt\Acl\Test;

use Konekt\Acl\Exceptions\GuardDoesNotMatch;
use Konekt\Acl\Exceptions\PermissionDoesNotExist;
use Konekt\Acl\Exceptions\RoleDoesNotExist;
use Konekt\Acl\Models\RoleProxy;

class HasRolesTest extends TestCase
{
    /** @test */
    public function it_can_determine_that_the_user_does_not_have_a_role()
    {
        $this->assertFalse($this->testUser->hasRole('testRole'));
    }

    /** @test */
    public function it_can_assign_and_remove_a_role()
    {
        $this->testUser->assignRole('testRole');

        $this->assertTrue($this->testUser->hasRole('testRole'));

        $this->testUser->removeRole('testRole');

        $this->refreshTestUser();

        $this->assertFalse($this->testUser->hasRole('testRole'));
    }

    /** @test */
    public function it_can_assign_and_remove_a_role_on_a_permission()
    {
        $this->testUserPermission->assignRole('testRole');

        $this->assertTrue($this->testUserPermission->hasRole('testRole'));

        $this->testUserPermission->removeRole('testRole');

        $this->refreshTestUserPermission();

        $this->assertFalse($this->testUserPermission->hasRole('testRole'));
    }

    /** @test */
    public function it_can_assign_a_role_using_an_object()
    {
        $this->testUser->assignRole($this->testUserRole);

        $this->assertTrue($this->testUser->hasRole($this->testUserRole));
    }

    /** @test */
    public function it_can_assign_a_role_using_an_id()
    {
        $this->testUser->assignRole($this->testUserRole->id);

        $this->assertTrue($this->testUser->hasRole($this->testUserRole));
    }

    /** @test */
    public function it_can_assign_multiple_roles_at_once()
    {
        $this->testUser->assignRoles($this->testUserRole->id, 'testRole2');

        $this->assertTrue($this->testUser->hasRole('testRole'));

        $this->assertTrue($this->testUser->hasRole('testRole2'));
    }

    /** @test */
    public function it_can_assign_multiple_roles_using_an_array()
    {
        $this->testUser->assignRoles($this->testUserRole->id, 'testRole2');

        $this->assertTrue($this->testUser->hasRole('testRole'));
        $this->assertTrue($this->testUser->hasRole('testRole2'));
    }

    /** @test */
    public function it_throws_an_exception_when_assigning_a_role_that_does_not_exist()
    {
        $this->expectException(RoleDoesNotExist::class);

        $this->testUser->assignRole('evil-emperor');
    }

    /** @test */
    public function it_can_only_assign_roles_from_the_correct_guard()
    {
        $this->expectException(RoleDoesNotExist::class);

        $this->testUser->assignRole('testAdminRole');
    }

    /** @test */
    public function it_throws_an_exception_when_assigning_a_role_from_a_different_guard()
    {
        $this->expectException(GuardDoesNotMatch::class);

        $this->testUser->assignRole($this->testAdminRole);
    }

    /** @test */
    public function it_can_sync_roles_from_a_string()
    {
        $this->testUser->assignRole('testRole');
        $this->testUser->syncRoles('testRole2');

        $this->assertFalse($this->testUser->hasRole('testRole'));

        $this->assertTrue($this->testUser->hasRole('testRole2'));
    }

    /** @test */
    public function it_can_sync_roles_from_a_string_on_a_permission()
    {
        $this->testUserPermission->assignRole('testRole');

        $this->testUserPermission->syncRoles('testRole2');

        $this->assertFalse($this->testUserPermission->hasRole('testRole'));

        $this->assertTrue($this->testUserPermission->hasRole('testRole2'));
    }

    /** @test */
    public function it_can_sync_multiple_roles()
    {
        $this->testUser->syncRoles('testRole', 'testRole2');

        $this->assertTrue($this->testUser->hasRole('testRole'));
        $this->assertTrue($this->testUser->hasRole('testRole2'));
    }

    /** @test */
    public function it_will_remove_all_roles_when_no_argument_is_passed_to_sync_roles()
    {
        $this->testUser->assignRole('testRole');

        $this->testUser->assignRole('testRole2');

        $this->testUser->syncRoles();

        $this->assertFalse($this->testUser->hasRole('testRole'));

        $this->assertFalse($this->testUser->hasRole('testRole2'));
    }

    /** @test */
    public function it_throws_an_exception_when_syncing_a_role_from_another_guard()
    {
        $this->expectException(RoleDoesNotExist::class);

        $this->testUser->syncRoles('testRole', 'testAdminRole');

        $this->expectException(GuardDoesNotMatch::class);

        $this->testUser->syncRoles('testRole', $this->testAdminRole);
    }

    /** @test */
    public function it_deletes_pivot_table_entries_when_deleting_models()
    {
        $user = User::create(['email' => 'user@test.com']);

        $user->assignRole('testRole');
        $user->givePermissionTo('edit-articles');

        $this->assertDatabaseHas('model_permissions', ['model_id' => $user->id]);
        $this->assertDatabaseHas('model_roles', ['model_id' => $user->id]);

        $user->delete();

        $this->assertDatabaseMissing('model_permissions', ['model_id' => $user->id]);
        $this->assertDatabaseMissing('model_roles', ['model_id' => $user->id]);
    }

    /** @test */
    public function it_can_scope_users_using_a_string()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->assignRole('testRole');
        $user2->assignRole('testRole2');

        $scopedUsers1 = User::havingRole($this->testUserRole)->get();

        $this->assertEquals($scopedUsers1->count(), 1);
    }

    /** @test */
    public function it_can_scope_users_using_multiple_roles()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->assignRole($this->testUserRole);
        $user2->assignRole('testRole2');

        $scopedUsers1 = User::havingRole($this->testUserRole)->get();
        $scopedUsers2 = User::havingRoles('testRole', 'testRole2')->get();

        $this->assertEquals($scopedUsers1->count(), 1);
        $this->assertEquals($scopedUsers2->count(), 2);
    }

    /** @test */
    public function it_can_scope_users_using_an_object()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->assignRole($this->testUserRole);
        $user2->assignRole('testRole2');

        $scopedUsers = User::havingRole($this->testUserRole)->get();

        $this->assertEquals($scopedUsers->count(), 1);
    }

    /** @test */
    public function it_throws_an_exception_when_trying_to_scope_a_role_from_another_guard()
    {
        $this->expectException(RoleDoesNotExist::class);

        User::havingRole('testAdminRole')->get();

        $this->expectException(GuardDoesNotMatch::class);

        User::havingRole($this->testAdminRole)->get();
    }

    /** @test */
    public function it_can_determine_that_a_user_has_one_of_the_given_roles()
    {
        RoleProxy::create(['name' => 'second role']);

        $this->assertFalse($this->testUser->hasRole(RoleProxy::all()));

        $this->testUser->assignRole($this->testUserRole);

        $this->refreshTestUser();

        $this->assertTrue($this->testUser->hasRole(RoleProxy::all()));

        $this->assertTrue($this->testUser->hasAnyRole(RoleProxy::all()));

        $this->assertTrue($this->testUser->hasAnyRole('testRole'));

        $this->assertFalse($this->testUser->hasAnyRole('role does not exist'));

        $this->assertTrue($this->testUser->hasAnyRole(['testRole']));

        $this->assertTrue($this->testUser->hasAnyRole(['testRole', 'role does not exist']));

        $this->assertFalse($this->testUser->hasAnyRole(['role does not exist']));

        $this->assertTrue($this->testUser->hasAnyRole('testRole', 'role does not exist'));
    }

    /** @test */
    public function it_can_determine_that_a_user_has_all_of_the_given_roles()
    {
        $this->assertFalse($this->testUser->hasAllRoles(RoleProxy::first()));

        $this->assertFalse($this->testUser->hasAllRoles('testRole'));

        $this->assertFalse($this->testUser->hasAllRoles(RoleProxy::all()));

        RoleProxy::create(['name' => 'second role']);

        $this->testUser->assignRole($this->testUserRole);

        $this->refreshTestUser();

        $this->assertFalse($this->testUser->hasAllRoles(['testRole', 'second role']));

        $this->testUser->assignRole('second role');

        $this->refreshTestUser();

        $this->assertTrue($this->testUser->hasAllRoles(['testRole', 'second role']));
    }

    /** @test */
    public function it_can_determine_that_a_user_does_not_have_a_role_from_another_guard()
    {
        $this->assertFalse($this->testUser->hasRole('testAdminRole'));

        $this->assertFalse($this->testUser->hasRole($this->testAdminRole));

        $this->testUser->assignRole('testRole');

        $this->refreshTestUser();

        $this->assertTrue($this->testUser->hasAnyRole(['testRole', 'testAdminRole']));

        $this->assertFalse($this->testUser->hasAnyRole('testAdminRole', $this->testAdminRole));
    }

    /** @test */
    public function it_can_determine_that_the_user_does_not_have_a_permission()
    {
        $this->assertFalse($this->testUser->hasPermissionTo('edit-articles'));
    }

    /** @test */
    public function it_throws_an_exception_when_the_permission_does_not_exist()
    {
        $this->expectException(PermissionDoesNotExist::class);

        $this->testUser->hasPermissionTo('does-not-exist');
    }

    /** @test */
    public function it_throws_an_exception_when_the_permission_does_not_exist_for_this_guard()
    {
        $this->expectException(PermissionDoesNotExist::class);

        $this->testUser->hasPermissionTo('admin-permission');
    }

    /** @test */
    public function it_can_work_with_a_user_that_does_not_have_any_permissions_at_all()
    {
        $user = new User();

        $this->assertFalse($user->hasPermissionTo('edit-articles'));
    }

    /** @test */
    public function it_can_determine_that_the_user_has_any_of_the_permissions_directly()
    {
        $this->assertFalse($this->testUser->hasAnyPermission('edit-articles'));

        $this->testUser->givePermissionTo('edit-articles');

        $this->refreshTestUser();

        $this->assertTrue($this->testUser->hasAnyPermission('edit-news', 'edit-articles'));

        $this->testUser->givePermissionTo('edit-news');

        $this->refreshTestUser();

        $this->testUser->revokePermissionTo($this->testUserPermission);

        $this->assertTrue($this->testUser->hasAnyPermission('edit-articles', 'edit-news'));
    }

    /** @test */
    public function it_can_determine_that_the_user_has_any_of_the_permissions_directly_using_an_array()
    {
        $this->assertFalse($this->testUser->hasAnyPermission(['edit-articles']));

        $this->testUser->givePermissionTo('edit-articles');

        $this->refreshTestUser();

        $this->assertTrue($this->testUser->hasAnyPermission(['edit-news', 'edit-articles']));

        $this->testUser->givePermissionTo('edit-news');

        $this->refreshTestUser();

        $this->testUser->revokePermissionTo($this->testUserPermission);

        $this->assertTrue($this->testUser->hasAnyPermission(['edit-articles', 'edit-news']));
    }

    /** @test */
    public function it_can_determine_that_the_user_has_any_of_the_permissions_via_role()
    {
        $this->testUserRole->givePermissionTo('edit-articles');

        $this->testUser->assignRole('testRole');

        $this->assertTrue($this->testUser->hasAnyPermission('edit-news', 'edit-articles'));
    }

    /** @test */
    public function it_can_determine_that_user_has_direct_permission()
    {
        $this->testUser->givePermissionTo('edit-articles');
        $this->refreshTestUser();
        $this->assertTrue($this->testUser->hasDirectPermission('edit-articles'));
        $this->testUser->revokePermissionTo('edit-articles');
        $this->refreshTestUser();
        $this->assertFalse($this->testUser->hasDirectPermission('edit-articles'));

        $this->testUser->assignRole('testRole');
        $this->testUserRole->givePermissionTo('edit-articles');
        $this->refreshTestUser();
        $this->assertFalse($this->testUser->hasDirectPermission('edit-articles'));
    }

    /** @test */
    public function it_can_list_all_the_permissions_via_his_roles()
    {
        RoleProxy::findByName('testRole2')->givePermissionTo('edit-news');

        $this->testUserRole->givePermissionTo('edit-articles');
        $this->testUser->assignRoles('testRole', 'testRole2');

        $this->assertEquals(
            collect(['edit-articles', 'edit-news']),
            $this->testUser->getPermissionsViaRoles()->pluck('name')
        );
    }

    /** @test */
    public function it_can_list_all_the_coupled_permissions_both_directly_and_via_roles()
    {
        $this->testUser->givePermissionTo('edit-news');

        $this->testUserRole->givePermissionTo('edit-articles');
        $this->testUser->assignRole('testRole');

        $this->assertEquals(
            collect(['edit-articles', 'edit-news']),
            $this->testUser->getAllPermissions()->pluck('name')
        );
    }

    /** @test */
    public function it_can_retrieve_role_names()
    {
        $this->testUser->assignRoles('testRole', 'testRole2');

        $this->assertEquals(
            collect(['testRole', 'testRole2']),
            $this->testUser->getRoleNames()
        );
    }

    /** @test */
    public function it_does_not_detach_roles_when_soft_deleting()
    {
        $user = SoftDeletingUser::create(['email' => 'test@example.com']);
        $user->assignRole('testRole');
        $user->delete();

        $user = SoftDeletingUser::withTrashed()->find($user->id);

        $this->assertTrue($user->hasRole('testRole'));
    }
}

# Blade Directives

This package also adds Blade directives to verify whether the currently logged-in user has all or any of a given list of roles.

Optionally you can pass in the `guard` that the check will be performed on as a second argument.

## Blade and Roles

Test for a specific role:

```blade
@role('writer')
    I am a writer!
@else
    I am not a writer...
@endrole
```

is the same as

```blade
@hasrole('writer')
    I am a writer!
@else
    I am not a writer...
@endhasrole
```

Test for any role in a list:

```blade
@hasanyrole($collectionOfRoles)
    I have one or more of these roles!
@else
    I have none of these roles...
@endhasanyrole
<!-- or -->
@hasanyrole('writer|admin')
    I am either a writer or an admin or both!
@else
    I have none of these roles...
@endhasanyrole
```

Test for all roles:

```blade
@hasallroles($collectionOfRoles)
    I have all of these roles!
@else
    I do not have all of these roles...
@endhasallroles
// or
@hasallroles('writer|admin')
    I am both a writer and an admin!
@else
    I do not have all of these roles...
@endhasallroles
```

## Blade and Permissions

This package doesn't add any permission-specific Blade directives. Instead, use Laravel's native `@can` directive to
check if a user has a certain permission.

```blade
@can('edit articles')
  //
@endcan
```

or

```blade
@if(auth()->user()->can('edit articles') && $some_other_condition)
  //
@endif
```

---

**Next**: [Multiple Guards &raquo;](multiple-guards.md)

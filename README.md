# last-in-last-out
A wordpress plugin to display posts in admin in "last in last out" philosophy by adjusting menu_order property 

For Developers
Post type can be added through filter hook `lilo_post_types`

Example
```
function add_custom_post_as_lilo($post_types)
{
    $post_types[] =  'page';
    return $post_types;
}
add_filter('lilo_post_types','add_custom_post_as_lilo');
```

Post type can also be select via option page under Setting called `LiLo` (for Last In Last Out)

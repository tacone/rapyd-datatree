# DataTree for Rapyd

If you use Laravel 5 you don't need this repository, the datatree has been
merged in the core of [Rapyd](https://github.com/zofe/rapyd-laravel/)!

If you use Laravel 4.2 and Rapyd you can use this repo to add the DataTree
to your app.

## Installation

Before beginning, you need to add to your composer Baum\Baum as we don't add it
automatically for you.

Add it to composer, add the service provider and publish the assets.

Add the service provider to app.php
```
        'Tacone\RapydDataTree\RapydDataTreeServiceProvider',
```

Add the css and js hooks in your template:

In the `<head>`:
```
    {{ DataTree::styles() }}

```

At the end of the `<body>`:
```
    {{ DataTree::scripts() }}
```

## Sample usage

```php
use Tacone\RapydDataTree\DataTree;

class MyController extends Controller
{
    public function anyIndex($rootId = null)
    {
        $rootId or App::abort(404);
        $root = Menu::find($rootId) or App::abort(404);

        $tree = DataTree::source($root);
        $tree->add('title');
        $tree->edit("/admin/menu/edit", 'Edit', 'modify|delete');

        return View::make('admin/menu/index', compact('tree'));
    }
}
```
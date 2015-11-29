# DataTree for Rapyd

This is a sortable widget only compatible with Laravel 4 and Rapyd 1.3.x.
Hopefully once completed it will be merged in the master of Rapyd so that
Laravel 5 users will be able to use it as well.

## Installation

Add it to composer, add the service provider and publish the assets.

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
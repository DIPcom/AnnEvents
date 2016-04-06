#AnnEvents for Kdyby Events

AnnEvents expanding [Kdyby Events](https://github.com/Kdyby/Events). Adds the ability to create events by using annotations.


Installation
------------
The best way to install DIPcom/AnnEvents is using  [Composer](http://getcomposer.org/):
```sh
$ composer require dipcom/annevents
```

Minimal configuration
---------------------

```yaml
extensions:
        events: Kdyby\Events\DI\EventsExtension
        doctrine: Kdyby\Doctrine\DI\OrmExtension
	annevents: DIPcom\AnnEvents\DI\AnnEventsExtension
```

Use
---

####Annotations

* `@Event()` Event class
* `@Listener()` Listener event
* `@On()` Property interconnection point
* `Target(listener="class", referenced="property")` Target listener and its parameters `listener="class"` and `referenced="property"`

Before using AnnEvents docs [Kdyby Events](https://github.com/Kdyby/Events/blob/master/docs/en/index.md).

####Create **listener** class

```php 
namespace App\Events;

use Nette;
use DIPcom\AnnEvents\Mapping as EV;

/**
 * @EV\Listener()
 */
class Order extends Nette\Object{

    /**
     * @EV\On()
     */
    public $onCreateOrder = array();

    /**
     * @see \App\Models\Order::createOrder()
     * @param array $items
     */
    public function pushBillItem(array $items){
        $this->onPushBillItem($items, $this);
        
    }

}
```

####Create **event** class

```php 
namespace App\Events;

use Nette;
use DIPcom\AnnEvents\Mapping as EV;
use App\Events\Order;

/**
 * @EV\Event()
 */
class OrderEvent extends Nette\Object{

    /**
     * @EV\On()
     */
    public $onCreateOrder = array();

    /**
     * @EV\Targe(listener="App\Events\Order", referenced="onCreateOrder")
     */
    public function myAction(array $items, Order $listener){
        dump($items);
    }

}
```

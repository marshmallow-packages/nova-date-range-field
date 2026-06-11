![alt text](https://marshmallow.dev/cdn/media/logo-red-237x46.png "marshmallow.")

# Date Range Field for Laravel Nova

[![Latest Version on Packagist](https://img.shields.io/packagist/v/marshmallow/nova-date-range-field.svg?style=flat-square)](https://packagist.org/packages/marshmallow/nova-date-range-field)
[![Total Downloads](https://img.shields.io/packagist/dt/marshmallow/nova-date-range-field.svg?style=flat-square)](https://packagist.org/packages/marshmallow/nova-date-range-field)

A Laravel Nova field that provides a date range picker (powered by [flatpickr](https://flatpickr.js.org/)). It can store a range across two model attributes (a "from" and a "till"), as a single value, or as JSON.

## Installation

Install the package via Composer in your Nova project:

```bash
composer require marshmallow/nova-date-range-field
```

The field's assets are registered automatically through `Marshmallow\NovaDateRangeField\FieldServiceProvider` (Laravel package auto-discovery), so there is nothing else to publish or configure.

## Usage

Use the `DateRange` field on a Nova resource and define the names of the `from` and `till` attributes together with the label. If no fields are set, the string value is stored in the database column of the field's attribute.

```php
use Marshmallow\NovaDateRangeField\DateRange;

public function fields(Request $request)
{
    return [
        DateRange::make(__('Access Date'), ['from', 'till']),
        // OR
        DateRange::make(__('Access Date'))
            ->fields('from', 'till'),
    ];
}
```

### flatpickr options

Additional [flatpickr options](https://flatpickr.js.org/options/) can be passed through the `options` array. These overwrite the field's default options (`weekNumbers`, `defaultHour`, `defaultMinute`).

```php
DateRange::make(__('Date range'))
    ->fields('from', 'till')
    ->options([
        'defaultHour' => 0,
        'defaultMinute' => 0,
    ]),
```

### Field options

The available chainable options (with their default behaviour) are:

```php
->modeType('range')   // Set the picker mode ('range' or 'single')
->range()             // Range mode (default)
->single()            // Single date mode
->twelveHourTime()    // Twelve hour time display
->enableSeconds()     // Enable seconds in the time picker
->separator('-')      // Separator shown between the from/till values
->firstDayOfWeek(1)   // First day of the week
->enableTime()        // Enable the time picker
->dateFormat('Y-m-d') // flatpickr date format
->placeholder('date range')
->saveAsJSON()        // Store the value as JSON instead of separate columns
```

When `saveAsJSON()` is used (or when the field's attribute is cast to `array` on the model), the range is stored as JSON on a single attribute instead of being split across the `from` and `till` columns.

## Credits

- [Marshmallow](https://github.com/marshmallow-packages)
- [All Contributors](https://github.com/marshmallow-packages/nova-date-range-field/contributors)

## Security Vulnerabilities

Please report security vulnerabilities by email rather than via the public issue tracker.

## License

The MIT License (MIT). Please see the [License File](LICENSE.md) for more information.

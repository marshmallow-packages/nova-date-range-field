## Date Range Field for Laravel Nova

Nova field for a Date Range Picker.

### Install

Run this command in your nova project:
`composer require marshmallow/nova-date-range-field`

### How to use

Use DateRange Field class and define the name of the (from & till) fields and the label. If no fields are set the string value will be stored in the corresponding database column of the attribute.

```php
    use Marshmallow\NovaDateRangeField\DateRange;

    public function fields(Request $request)
    {
        return [
             DateRange::make(__('Access Date'))
                ->fields('from', 'till'),
        ];
    }
```

Aditional options from [flatpickr](https://flatpickr.js.org/options/) can be used by adding them in the options array. These will overwrite the default options.

```php
     DateRange::make(__('Date range'))
        ->fields('from', 'till')
        ->options([
            'defaultHour' => 0,
            'defaultMinute' => 0,
        ]),
```

Other field options (with their default values) are:

```php
    ->modeType('range')
    ->range() // Default mode
    ->single() // default is disabled
    ->twelveHourTime() // default is disabled
    ->enableSeconds() // default is disabled
    ->separator('-')
    ->firstDayOfWeek(1)
    ->enableTime() // default is disabled
    ->dateFormat('Y-m-d')
    ->placeholder('date range')
    ->saveAsJSON() // default is disabled
```

<?php

namespace Marshmallow\NovaDateRangeField;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\SupportsDependentFields;

class DateRange extends Field
{
    use SupportsDependentFields;

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'nova-date-range-field';

    protected $saveAsJSON = false;
    public $fields_set = false;

    public $from_field;
    public $till_field;
    public $options = [];


    public function __construct($name, $attribute = null, ?callable $resolveCallback = null)
    {
        if (is_array($name)) {
            $this->fields_set = true;
            $name = implode('-', $name);
        }
        if (is_array($attribute)) {
            $this->fields_set = true;
            $attribute = implode('-', $attribute);
        }

        parent::__construct($name, $attribute, $resolveCallback);
    }

    /**
     * Parse the attribute name to retrieve the affected model attributes
     *
     * @param $attribute
     * @return array
     */
    protected function parseAttribute($attribute)
    {
        return explode('-', $attribute);
    }

    /**
     * Resolve the field's value.
     *
     * @param  mixed  $resource
     * @param  string|null  $attribute
     * @return void
     */
    public function resolveAttribute($resource, string $attribute): mixed
    {

        $singleField = false;

        if (Arr::has($this->meta, 'modeType') && $this->meta['modeType'] == 'single') {
            $singleField = true;
        }

        if (!$singleField && !is_array($attribute) && !Str::contains($attribute, '-')) {
            $singleField = true;
        } elseif (!$this->fields_set || (!$this->from_field && !$this->till_field)) {
            [$this->from_field, $this->till_field] = $this->parseAttribute($attribute);
        }

        if ($singleField) {
            return Carbon::parse($resource->$attribute);
        }

        $attribute = $attribute ?? $this->attribute;

        if ($this->from_field && $this->till_field) {
            $separator = $this->meta['separator'] ?? '-';
            $time_enabled = $this->meta['enableTime'] ?? true;
            $format = $this->meta['format'] ??  ($time_enabled ? "Y-m-d H:i" : "Y-m-d");

            $from_value = $resource->{$this->from_field};
            $till_value = $resource->{$this->till_field};

            $value = '';
            if ($from_value) {
                $value = Carbon::parse($from_value)->format($format);
            }
            if ($till_value) {
                $value .= " {$separator} ";
                $value .= Carbon::parse($till_value)->format($format);
            }

            return $value ?? null;
        }

        return null;
    }

    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        $valid_range = false;
        $singleField = false;


        if (Arr::has($this->meta, 'modeType') && $this->meta['modeType'] == 'single') {
            $singleField = true;
        }

        if ($mode = Arr::get($this->meta, 'modeType')) {
            $singleField = $mode == 'single';
        }

        if (!$singleField && !$this->fields_set) {
            [$this->from_field, $this->till_field] = $this->parseAttribute($attribute);
        }

        $value = $request->input($requestAttribute) ?: null;

        if ($value && $this->isJson($value)) {
            $value = json_decode($value, true);
            if (Arr::has($value, ['from', 'till'])) {
                $valid_range = true;
            }
        }

        $saveAsJson = $this->shouldSaveAsJson($model, $attribute);

        if ($singleField && !$valid_range) {
            $model->{$attribute} = $value;
        } elseif ($valid_range && $this->from_field && $this->till_field) {
            $value = is_null($value) ? ($this->nullable ? $value : $value = []) : $value;
            $from_value = $value['from'] ?? null;
            $till_value = $value['till'] ?? null;

            if ($from_value) {
                $model->{$this->from_field} = Carbon::parse($from_value);
            } else {
                $model->{$this->from_field} = null;
            }

            if ($till_value) {
                $model->{$this->till_field} = Carbon::parse($till_value);
            } else {
                $model->{$this->till_field} = null;
            }
        } elseif (isset($model->{$attribute})) {
            if ($valid_range) {
                $saveAsJson = true;
            }
            $value = is_null($value) ? ($this->nullable ? $value : $value = []) : $value;
            $model->{$attribute} = ($saveAsJson || is_null($value)) ? $value : json_encode($value);
        }
    }

    private function isJson($str)
    {
        $json = json_decode($str);
        return $json && $str != $json;
    }

    private function shouldSaveAsJson($model, $attribute)
    {
        if (!is_array($model) && method_exists($model, 'getCasts')) {
            $casts = $model->getCasts();
            $isCastedToArray = ($casts[$attribute] ?? null) === 'array';
            return $this->saveAsJSON || $isCastedToArray;
        }
        return false;
    }

    public function getOptions()
    {
        if ($this->options) {
            return $this->options;
        }

        return [
            'weekNumbers' => true,
            'defaultHour' => 0,
            'defaultMinute' => 0,
        ];
    }

    public function saveAsJSON($saveAsJSON = true)
    {
        $this->saveAsJSON = $saveAsJSON;
        return $this;
    }

    public function fields($from, $till)
    {
        $this->from_field = $from;
        $this->till_field = $till;
        $this->fields_set = true;
        return $this;
    }

    public function options(array $options)
    {
        $this->options = $options;
        $this->withMeta(['options' => $options]);
        return $this;
    }

    public function modeType($mode = 'range')
    {
        $this->withMeta(['modeType' => $mode]);
        return $this;
    }

    public function range()
    {
        $this->withMeta(['modeType' => 'range']);
        return $this;
    }

    public function single()
    {
        $this->withMeta(['modeType' => 'single']);
        return $this;
    }

    public function twelveHourTime()
    {
        $this->withMeta(['twelveHourTime' => false]);
        return $this;
    }

    public function enableSeconds()
    {
        $this->withMeta(['enableSeconds' => false]);
        return $this;
    }

    public function separator()
    {
        $this->withMeta(['separator' => '-']);
        return $this;
    }

    public function firstDayOfWeek($day = 1)
    {
        $this->withMeta(['firstDayOfWeek' => $day]);
        return $this;
    }

    public function enableTime()
    {
        $this->withMeta(['enableTime' => true]);
        return $this;
    }

    public function dateFormat($format)
    {
        $this->withMeta(['dateFormat' => $format]);
        return $this;
    }

    public function placeholder($placeholder)
    {
        $this->withMeta(['placeholder' => $placeholder]);
        return $this;
    }


    /**
     * Prepare the element for JSON serialization.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $request = app(NovaRequest::class);

        if ($request->isFormRequest()) {
            return array_merge(parent::jsonSerialize(), [
                'options' => $this->getOptions(),
            ]);
        }

        return (parent::jsonSerialize());
    }
}

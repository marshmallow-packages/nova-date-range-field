<?php

namespace Marshmallow\NovaDateRangeField;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class DateRange extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'nova-date-range-field';

    protected $saveAsJSON = false;

    public $from_field;
    public $till_field;
    public $options;

    /**
     * Resolve the field's value.
     *
     * @param  mixed  $resource
     * @param  string|null  $attribute
     * @return void
     */
    public function resolve($resource, $attribute = null)
    {
        $this->resource = $resource;

        $attribute = $attribute ?? $this->attribute;

        if ($attribute === 'ComputedField') {
            $this->value = call_user_func($this->computedCallback, $resource);

            return;
        }

        if (!$this->resolveCallback && $this->from_field && $this->till_field) {
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

            $this->value = $value ?? null;
            return;
        }

        if (!$this->resolveCallback) {
            $this->value = $this->resolveAttribute($resource, $attribute);
        } elseif (is_callable($this->resolveCallback)) {
            tap($this->resolveAttribute($resource, $attribute), function ($value) use ($resource, $attribute) {
                $this->value = call_user_func($this->resolveCallback, $value, $resource, $attribute);
            });
        }
    }

    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        $valid_range = false;
        $singleField = false;

        if (Arr::has($this->meta, 'single')) {
            $singleField = true;
        }

        if ($mode = Arr::get($this->meta, 'modeType')) {
            $singleField = $mode == 'single';
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
        } else {
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
        return $this;
    }

    public function options(array $options)
    {
        $this->options = $options;
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

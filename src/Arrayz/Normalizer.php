<?php
namespace Daison\Arrayz;

class Normalizer
{
    private $records;

    public function __construct($records)
    {
        $this->records = $records;
    }

    public function reIndex($template, $with_header = false)
    {
        $new_records = [];

        if ($with_header) {

            $header = [];

            foreach ($template as $old_index => $new_index) {
                $header[$new_index] = $new_index;
            }

            array_unshift($this->records, $header);
        }

        foreach ($this->records as $idx => $record) {

            $new_record = [];

            foreach ($template as $old_index => $new_index) {

                if (!isset($record[$old_index])) {
                    $new_record[$new_index] = '';

                    continue;
                }

                if (is_array($record[$old_index])) {
                    $new_record[$new_index] = $this->reIndex($record[$old_index]);

                    continue;
                }

                $new_record[$new_index] = $record[$old_index];
            }

            $this->records[$idx] = $new_record;
        }

        return $this;
    }

    public function transform(
        $record_indexes,
        $pattern = array(false => 'No', true => 'Yes')
    ) {
        $new_records = [];

        foreach ($this->records as $idx => $record) {

            foreach ($record as $key => $val) {

                if (in_array($key, $record_indexes)) {

                    if (!isset($pattern[$val])) {
                        continue;
                    }

                    $record[$key] = $pattern[$val];
                }
            }

            $this->records[$idx] = $record;
        }

        return $this;
    }

    public function removeIndex($index, $record = [])
    {
        if (!empty($record)) {

            if (isset($record[$index])) {
                unset($record[$index]);
            }

            return $record;
        }

        foreach ($this->records as $idx => $record) {

            if (isset($this->records[$idx][$index])) {
                unset($this->records[$idx][$index]);
            }
        }

        return $this;
    }

    public function removeIndexes($keys)
    {
        foreach ($keys as $key) {

            foreach ($this->records as $idx => $record) {

                $this->records[$idx] = $this->removeIndex($key, $record);
            }
        }

        return $this;
    }

    public function addIndex($new_index, $after_index, $value = null)
    {
        $indexed_at = 0;

        foreach ($this->records as $record) {

            $counter = 1;

            foreach ($record as $key => $val) {

                if ($after_index == $key) {
                    $indexed_at = $counter;

                    break 2;
                }

                $counter++;
            }
        }

        foreach ($this->records as $idx => $record) {
            $last_record = array_splice($record, $indexed_at);

            $record[$new_index]  = $value;

            $this->records[$idx] =  array_merge($record, $last_record);
        }

        return $this;
    }

    public function filter($search, $key, $callback)
    {
        $new_records = [];

        foreach ($this->records as $idx => $record) {

            $extracted_val = $this->records[$idx][$key];

            if (strpos($extracted_val, $search) !== false) {
                $new_records[$idx] = $this->records[$idx];
            }
        }

        $self = new self($new_records);

        $new_data = call_user_func($callback, $self)->get();

        foreach ($new_data as $idx => $val) {
            $this->records[$idx] = $val;
        }

        return $this;
    }

    public function change($index, $value)
    {
        foreach ($this->records as $idx => $record) {
            $this->records[$idx][$index] = $value;
        }

        return $this;
    }

    public function setData($data)
    {
        $this->records = $data;

        return $this;
    }

    public function get()
    {
        return $this->records;
    }
}


$norm = new Normalizer([
    [
        'id'         => 1,
        'first_name' => 'Daison',
        'last_name'  => 'Carino',
        'age'        => 24,
        'address'    => '#21 Malunggay St. Project 7, Quezon City, Pangasinan',
        'is_married' => false,
    ],
    [
        'id'         => 2,
        'first_name' => 'Nissan Mae',
        'last_name'  => 'Dela Cruz',
        'age'        => 25,
        'address'    => 'Calobaoan, San Carlos Pangasinan',
        'is_married' => false,
    ],
    [
        'id'         => 3,
        'first_name' => 'Daison Lancer',
        'last_name'  => 'Carino',
        'age'        => 6,
        'address'    => 'Calobaoan, San Carlos Pangasinan',
    ],
]);



$norm->filter('Daison', 'first_name', function ($A) use ($norm) {

    $A->filter('Carino', 'last_name', function ($B) use ($norm) {

        $B->removeIndex('id');

        return $B;
    });

    return $A;
});

$norm->addIndex($new_key = 'gender', $after_key = 'age');

var_dump($norm->get());exit;

$norm->transform(
    $indexes = ['is_married'],
    $pattern = [
        false => 'No',
        true  => 'Yes',
    ]
);

$norm->reIndex(
    $indexes = [
        'id'         => 'ID',
        'first_name' => 'First Name',
        'last_name'  => 'Last Name',
        'age'        => 'Age',
        'gender'     => 'Gender',
        'address'    => 'Address',
        'is_married' => 'Married',
    ]
);

var_dump($norm->get());

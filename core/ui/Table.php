<?php
namespace Environment\UI;

class Table {

    private $Data = [];
    private $Columns = [];

    private $onRecordClick = null;
    private $className = '';
    private $style = '';
    private $NumbererStart = 1;

    private $cssRowFunction;

    // Column = [ 'Name', 'Caption', 'Renderer(data)' ]

    private $ReservedColumnNames = [ 'UITableNumberer' ];

    private function getUITableValue($Column) {
        $v = '';
        switch ($Column) {
            case 'UITableNumberer':
                $v = $this->NumbererStart++;
                break;

            default:
                # code...
                break;
        }
        return $v;
    }

    public function &__construct(array $Columns) {
        $this->Columns = $Columns;
        $this->cssRowFunction = [ 'f' => function () { return ""; } ];
        return $this;
    }

    private function isSetColumn($ColumnName) {
        foreach ($this->Columns as $Column) {
            if($Column['Name'] == $ColumnName) return $Column;
        }

        return [];
    }

    public function &load(array $Records) {

        foreach ($Records as $index => $Record) {
            $Data = [];

            $indexes = [
                '__current' => $index,
                '__prev' => isset($Records[$index-1]) ? $Records[$index-1] : null,
                '__next' => isset($Records[$index+1]) ? $Records[$index+1] : null
            ];

            foreach ($Record as $key => $Column) {
                if(in_array($key, $this->ReservedColumnNames)) {
                    $Data[$key] = $this->getUITableValue($key);
                }
                else
                {
                    $tmp = $this->isSetColumn($key);
                    if(count($tmp) > 0) {
                        $Data[$key] = (isset($tmp['Renderer'])) ? $tmp['Renderer']($Record + $indexes) : $Record[$tmp['Name']];
                    }
                    else
                    {
                        $Data[$key] = $Column;
                    }
                }
            }

            foreach ($this->Columns as $key => $Column) {
                if(in_array($Column['Name'], $this->ReservedColumnNames)) {
                    $Data[$Column['Name']] = $this->getUITableValue($key);
                }
                else
                {
                    $tmp = $this->isSetColumn($Column['Name']);
                    if(count($tmp) > 0) {
                        $Data[$Column['Name']] = (isset($tmp['Renderer'])) ? $tmp['Renderer']($Record + $indexes) : $Record[$tmp['Name']];
                    }
                    else
                    {
                        $Data[$Column['Name']] = $Column;
                    }
                }
            }

            $Data['__current'] = $index;
            $this->Data[] = $Data;
        }

        //var_dump($this->Data);

        return $this;
    }

    public function &setClass($class) { $this->className = $class; return $this; }
    public function &setStyle($style) { $this->style = $style; return $this; }
    public function &setNumbererStart($start) { $this->NumbererStart = $start; return $this; }
    // "window.location.href = '{IDNews}'"

    private function prepareOnRecordClickFunction($Data) {
        $js = $this->onRecordClick;

        foreach ($Data as $key => $value) {
            $js = str_replace('{'.$key.'}', $value, $js);
        }
        return $js;
    }


    public function setCssRowFunction($f) {
        $this->cssRowFunction['f'] = $f;
        return $this;
    }

    private  function prepareCssFunction($Data) {
        return $this->cssRowFunction['f']($Data);
    }

    public function &setOnRecordClick($js) {
        $this->onRecordClick = $js;
        return $this;
    }

    public function getDataByIndex($index) {
        return $this->Data[$index];
    }

    public function &write() {

        ?>
    <table class = "<?php echo $this->className; ?>" style = "<?php echo $this->style; ?>">
        <thead>
        <tr>
            <?php foreach ($this->Columns as $Column) : ?>
                <th><?php echo $Column['Caption']; ?></th>
            <?php endforeach; ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach($this->Data as $Data) : ?>

            <tr<?php if(!is_null($this->onRecordClick)) : ?> style = "cursor: pointer;<?php echo $this->prepareCssFunction($Data); ?>" onclick = "<?php echo $this->prepareOnRecordClickFunction($Data); ?>"<?php else: ?> style = "<?php echo $this->prepareCssFunction($Data); ?>"<?php endif; ?>>
                <?php foreach($this->Columns as $Column) : ?>
                    <td<?php if(isset($Column['Style'])): ?> style="<?php echo $Column['Style']; ?>"<?php endif; ?>><?php echo $Data[$Column['Name']]; ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        </tbody></table><?php
        return $this;
    }

}

?>
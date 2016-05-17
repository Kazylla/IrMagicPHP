<?php

namespace Kazylla\IrMagic;

/**
 * Class IrMagic
 */
class IrMagic extends Ir
{
    /**
     * @param int $bank
     * @return array
     * @throws \Exception
     */
    public function dumpBank($bank = null)
    {
        if (!is_null($bank)) {
            $this->bank($bank);
        }
        $dump = [];
        for ($n = 0;$n <= 63;$n++) {
            $dump[] = $this->dump($n);
        }
        return $dump;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function dumpBankAll()
    {
        $dump = [];

        $pointNum = $this->information(Ir::INFORMATION_RECORD_POINTER);
        $bankNum = $this->information(Ir::INFORMATION_BANK);

        for ($b = 0;$b <= $bankNum;$b++) {
            $dump = array_merge($dump, $this->dumpBank($b));
        }
        $dump = array_slice($dump, 0, $pointNum);

        return $dump;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function stat()
    {
        $information = [
            'ver' => $this->information(Ir::INFORMATION_VERSION),
            'recordPointer' => $this->information(Ir::INFORMATION_RECORD_POINTER),
            'sigMaxH' => $this->information(Ir::INFORMATION_H_SIGNAL_MAX),
            'sigMinH' => $this->information(Ir::INFORMATION_H_SIGNAL_MIN),
            'sigMaxL' => $this->information(Ir::INFORMATION_L_SIGNAL_MAX),
            'sigMinL' => $this->information(Ir::INFORMATION_L_SIGNAL_MIN),
            'postScaler' => $this->information(Ir::INFORMATION_POSTSCALER),
            'bank' => $this->information(Ir::INFORMATION_BANK),
        ];
        $temperature = [
            'temperature' => $this->temperature(),
        ];
        $data = ['data' => []];
        $dump = $this->dumpBankAll();
        $data['data'] = implode(' ', $dump);

        return array_merge($information, $temperature, $data);
    }

    /**
     * @param string $fileName
     * @param int $postScaler
     * @param int $modulation
     * @return string
     * @throws \Exception
     */
    public function execCapture($fileName, $postScaler = 100, $modulation = Ir::MODULATION_38KHZ)
    {
        $this->reset();

        $this->modulation($modulation);
        $this->postScaler($postScaler);

        $bytes = $this->capture();
        if (is_numeric($bytes) && $bytes > 0) {
            $this->saveData($fileName, $modulation);
        }

        return $bytes;
    }

    /**
     * @param string $fileName
     */
    public function execPlay($fileName)
    {
        $this->loadData($fileName);
        $this->play();
    }

    /**
     * @param string $fileName
     * @throws \Exception
     */
    public function loadData($fileName)
    {
        $data = $this->loadDataInternal($fileName);

        $pointNum = count($data['data']);
        $bankNum = ceil($pointNum / 64);

        $this->reset();
        $this->modulation($data['freq']);
        $this->postScaler($data['postscale']);
        $this->recordPointer($pointNum);

        for ($b = 0;$b < $bankNum;$b++) {
            $this->bank($b);
            $offset = $b * 64;
            $limit = ($pointNum - $offset) < 64 ? ($pointNum - $offset) : 64;
            $dump = array_slice($data['data'], $offset, $limit);
            foreach ($dump as $num => $byte) {
                $this->write($num, $byte);
            }
        }
    }

    /**
     * @param $fileName
     * @return array
     * @throws \Exception
     */
    protected function loadDataInternal($fileName)
    {
        $json = file_get_contents($fileName);
        if ($json === false) {
            throw new \Exception($fileName. ': not found');
        }
        return json_decode($json, true);
    }

    /**
     * @param string $fileName
     * @param int $modulation
     * @throws \Exception
     */
    public function saveData($fileName, $modulation = Ir::MODULATION_38KHZ)
    {
        $dump = $this->dumpBankAll();
        $data = [
            'postscale' => $this->information(Ir::INFORMATION_POSTSCALER),
            'freq' => $modulation,
            'data' => $dump,
            'format' => 'raw',
        ];
        $this->saveDataInternal($fileName, $data);
    }

    /**
     * @param string $fileName
     * @param string $data
     */
    protected function saveDataInternal($fileName, $data)
    {
        $json = json_encode($data);
        file_put_contents($fileName, $json);
    }
}

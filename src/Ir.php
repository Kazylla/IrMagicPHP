<?php

namespace Kazylla\IrMagic;

/**
 * Class Ir
 */
class Ir extends Serial
{
    const LED_OFF = 0;
    const LED_ON = 1;

    const INFORMATION_VERSION = 0;
    const INFORMATION_RECORD_POINTER = 1;
    const INFORMATION_H_SIGNAL_MAX = 2;
    const INFORMATION_H_SIGNAL_MIN = 3;
    const INFORMATION_L_SIGNAL_MAX = 4;
    const INFORMATION_L_SIGNAL_MIN = 5;
    const INFORMATION_POSTSCALER = 6;
    const INFORMATION_BANK = 7;

    const MODULATION_36KHZ = 36;
    const MODULATION_38KHZ = 38;
    const MODULATION_40KHZ = 40;

    /**
     * @throws \Exception
     */
    public function reset()
    {
        $this->send('r,0');
        $response = $this->recv(2);
        $this->check($response);
        usleep(10000);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function version()
    {
        $this->send('v');
        $response = $this->recv();
        $this->check($response);
        return $response[0];
    }

    /**
     * @return float
     * @throws \Exception
     */
    public function temperature()
    {
        $this->send('t');
        $response = $this->recv();
        $this->check($response);
        $celsius = ((5 / 1024 * (int)$response[0]) - 0.4) / (19.53 / 1000);
        return $celsius;
    }

    /**
     * @param int $switch
     * @throws \Exception
     */
    public function led($switch)
    {
        if (!in_array($switch, [self::LED_OFF, self::LED_ON])) {
            throw new \Exception('Invalid parameter: ' . $switch);
        }
        $this->send('L,' . $switch);
        $response = $this->recv(2);
        $this->check($response);
    }

    /**
     * @param int $type
     * @return int
     * @throws \Exception
     */
    public function information($type)
    {
        if (!in_array($type, [
            self::INFORMATION_VERSION,
            self::INFORMATION_RECORD_POINTER,
            self::INFORMATION_H_SIGNAL_MAX,
            self::INFORMATION_H_SIGNAL_MIN,
            self::INFORMATION_L_SIGNAL_MAX,
            self::INFORMATION_L_SIGNAL_MIN,
            self::INFORMATION_POSTSCALER,
            self::INFORMATION_BANK,
        ])) {
            throw new \Exception('Invalid parameter: ' . $type);
        }
    
        $this->send('i,' . $type);
        $response = $this->recv(1);

        $value = $response[0];
        if (in_array($type, [
            self::INFORMATION_RECORD_POINTER,
            self::INFORMATION_H_SIGNAL_MAX,
            self::INFORMATION_H_SIGNAL_MIN,
            self::INFORMATION_L_SIGNAL_MAX,
            self::INFORMATION_L_SIGNAL_MIN,
        ])) {
            $value = hexdec($value);
        }

        return $value;
    }

    /**
     * @param int $number
     * @return int
     * @throws \Exception
     */
    public function dump($number)
    {
        if ($number < 0 || $number > 63) {
            throw new \Exception('Invalid parameter: ' . $number);
        }
        $this->send('d,' . $number);
        $response = $this->recv(1);
        return hexdec(rtrim($response[0]));
    }

    /**
     * @param $bank
     * @throws \Exception
     */
    public function bank($bank)
    {
        if ($bank < 0 || $bank > 9) {
            throw new \Exception('Invalid parameter: ' . $bank);
        }

        $this->send('b,' . $bank);
    }

    /**
     * @param int $value
     * @throws \Exception
     */
    public function postScaler($value)
    {
        if ($value < 1 || $value > 255) {
            throw new \Exception('Invalid parameter: ' . $value);
        }

        $this->send('k,' . $value);
        $response = $this->recv(2);
        $this->check($response);
    }

    /**
     * @param int $frequency
     * @throws \Exception
     */
    public function modulation($frequency)
    {
        if (!in_array($frequency, [
            self::MODULATION_36KHZ,
            self::MODULATION_38KHZ,
            self::MODULATION_40KHZ,
        ])) {
            throw new \Exception('Invalid parameter: ' . $frequency);
        }

        $value = ($frequency - self::MODULATION_36KHZ) / 2;
        $this->send('M,' . $frequency);
        $response = $this->recv(2);
        $this->check($response);
    }

    /**
     * @param int $number
     * @throws \Exception
     */
    public function recordPointer($number)
    {
        $this->send('N,' . $number);
        $response = $this->recv(1);
        $this->check($response);
    }

    /**
     * @return string
     */
    public function capture()
    {
        $this->send('c');
        $response = $this->recv(2);
        $capture = substr($response[0], 4);
        return $capture;
    }

    /**
     * @throws \Exception
     */
    public function play()
    {
        $this->send('P');
        $response = $this->recv(2);
        $this->check($response);
    }

    /**
     * @param int $position
     * @param int $data
     */
    public function write($position, $data)
    {
        $this->send('W,' . $position . ',' . $data);
    }

    /**
     * @param array $response
     * @throws \Exception
     */
    protected function check($response)
    {
        if ($response[count($response) - 2] != 'OK' && $response[count($response) - 2] != '... Done !') {
            throw new \Exception('Error response received from ir device: ' . $response[0]);
        }
    }
}

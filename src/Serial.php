<?php

namespace Kazylla\IrMagic;

/**
 * Class Serial
 */
class Serial
{
    protected $params = [
        'baud' => 9600,
        'bits' => 8,
        'stop' => 1,
        'parity' => 0,
    ];
    protected $fd = null;

    /**
     * Serial constructor.
     * @param string $device
     * @param array $params
     */
    public function __construct($device = '/dev/ttyACM0', $params = [])
    {
        if (!is_null($this->fd)) {
            return;
        }

        $this->params += $params;

        if (($this->fd = dio_open($device, O_RDWR | O_NOCTTY | O_NONBLOCK)) === false) {
            echo sprintf("cannot open device %s !\n", $device);
        }
        dio_fcntl($this->fd, F_SETFL, O_SYNC);
        dio_tcsetattr($this->fd, $params);
    }

    /**
     * @param string $message
     */
    public function send($message)
    {
        if (is_null($this->fd)) {
            return;
        }
        dio_write($this->fd, $message . "\n");
    }

    /**
     * @param int $expected
     * @return array|string
     */
    public function recv($expected = 3)
    {
        if (is_null($this->fd)) {
            return [];
        }

        $response = '';
        do {
            $response .= dio_read($this->fd);
        } while(count(explode("\r\n", $response)) < $expected);

        return explode("\r\n", $response);
    }

    /**
     *
     */
    public function close()
    {
        if (is_null($this->fd)) {
            return;
        }
        dio_close($this->fd);
        $this->fd = null;
    }
}

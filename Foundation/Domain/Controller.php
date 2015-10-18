<?php

class Controller {
    protected $server;
    protected $request;
    protected $response;

    protected $fd;
    protected $from_fd;


    public function __construct($server, $request, $response, $fd=0, $from_fd=0) {
        $this->server = $server;
        $this->request = $request;
        $this->response = $response;
        $this->fd = $fd;
        $this->from_fd = $from_fd;
    }
}

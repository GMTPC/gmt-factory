<?php

namespace Tests\Feature\CutBoard;

use Tests\TestCase;

class CutBoardAccessTest extends TestCase
{
    public function test_guest_is_redirected_to_login_from_cut_board_routes(): void
    {
        $this->get('/wms/cut-boards')->assertRedirect('/login');
        $this->get('/checker-qc-scan')->assertRedirect('/login');
        $this->get('/forklift-receive')->assertRedirect('/login');
        $this->get('/wms/bx-print')->assertRedirect('/login');
    }
}

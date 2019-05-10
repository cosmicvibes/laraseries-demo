<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Cosmicvibes\Laraseries\NumberSeries;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NumberSeriesTest extends TestCase
{
    use DatabaseMigrations;
    use RefreshDatabase;

    /**
     * Test the number series migrations have run
     *
     * @return void
     */
    public function testNumberSeriesTableExists(): void {

        $this->assertTrue(Schema::hasTable('number_series'));
    }

    public function testCanCreateManualNumberSeries(): void {
        $starting_number = 6;
        $ending_number = 500;
        $last_used_number = 10;
        $increment_by = 1;

        $data = [
            'code'              => 'TEST',
            'name'              => 'Test Number Series',
            'prefix'            => 'TEST',
            'suffix'            => 'Y',
            'length'            => 10,
            'increment_by'      => $increment_by,
            'padding_character' => 0,
            'start_date'        => Carbon::createFromFormat('d/m/Y', '01/01/2001'),
            'end_date'          => Carbon::createFromFormat('d/m/Y', '31/12/9999'),
            'active'            => true,
            'starting_number'   => $starting_number,
            'ending_number'     => $ending_number,
            'last_used_number'  => $last_used_number
        ];

        $numberSeries = new NumberSeries();
        $numberSeries->fill($data);

        $this->assertTrue($numberSeries->save());

        $this->assertEquals($starting_number, $numberSeries->starting_number);
        $this->assertEquals($ending_number, $numberSeries->ending_number);
        $this->assertEquals($last_used_number, $numberSeries->last_used_number);
        $this->assertEquals('TEST0000000010Y', $numberSeries->current);

        // Increment once
        $current_number = $numberSeries->advance();
        $this->assertNotNull($current_number);
        $this->assertEquals(($last_used_number + $increment_by), $numberSeries->last_used_number);
        $this->assertEquals('TEST0000000011Y', $current_number);
        $this->assertEquals('TEST0000000011Y', $numberSeries->current);

        // Increment to end
        // This is too slow
//        while ($numberSeries->last_used_number <= $numberSeries->ending_number) {
//            $numberSeries->advance();
//        }
        $numberSeries->advance($numberSeries->ending_number - $numberSeries->last_used_number - $numberSeries->increment_by);
        $this->assertEquals($numberSeries->ending_number, $numberSeries->last_used_number);
        $this->assertEquals(500, $numberSeries->last_used_number);
        $this->assertEquals('TEST0000000500Y', $numberSeries->current);

        // Check that we cannot advance further
        $this->assertNull($numberSeries->advance());

    }

    public function offtestCanCreateSemiAutoNumberSeries(): void {
        $starting_number = 0;
        $increment_by = 5;
        $data = [
            'code'              => 'TEST',
            'name'              => 'Test Number Series',
            'prefix'            => 'TEST',
            'suffix'            => 'Y',
            'length'            => 10,
            'increment_by'      => $increment_by,
            'padding_character' => '0',
            'start_date'        => Carbon::createFromFormat('d/m/Y', '01/01/2001'),
            'end_date'          => Carbon::createFromFormat('d/m/Y', '31/12/9999'),
            'active'            => true,
            'starting_number'   => $starting_number,
            // Let These calc
            'ending_number'     => null,
            'last_used_number'  => null,
        ];

        $numberSeries = new NumberSeries();
        $numberSeries->fill($data);

        $this->assertTrue($numberSeries->save());

        $this->assertEquals(9999999999, $numberSeries->ending_number);
        $this->assertEquals($numberSeries->starting_number, $numberSeries->last_used_number);
        $this->assertEquals('TEST0000000000Y', $numberSeries->current);

        // Increment once
        $current_number = $numberSeries->advance();
        $this->assertNotNull($current_number);
        $this->assertEquals((0 + $increment_by), $numberSeries->last_used_number);
        $this->assertEquals('TEST0000000005Y', $current_number);
        $this->assertEquals('TEST0000000005Y', $numberSeries->current);

        // Increment to end
        $numberSeries->advance($numberSeries->ending_number - $numberSeries->last_used_number - $numberSeries->increment_by);
        $this->assertEquals($numberSeries->ending_number, $numberSeries->last_used_number);
        $this->assertEquals(9999999999, $numberSeries->last_used_number);
        $this->assertEquals('TEST9999999999Y', $numberSeries->current);

        // Check that we cannot advance further
        $this->assertNull($numberSeries->advance());
    }
}

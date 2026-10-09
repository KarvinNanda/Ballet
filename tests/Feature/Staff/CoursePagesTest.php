<?php

namespace Tests\Feature\Staff;

use Illuminate\Support\Facades\DB;

class CoursePagesTest extends StaffTestCase
{
    public function test_list_shows_the_price_an_update_form_and_a_confirmed_delete(): void
    {
        $type = DB::table('class_types')->orderBy('id')->first();
        $row = $this->rowFor($this->asRole('admin')->get(route('admin.class-type.index'))->assertOk()->getContent(), $type->class_name);

        $this->assertStringContainsString('<td>Rp'.number_format($type->class_price).'</td>', $row);
        $this->assertStringContainsString('<form method="post" action="'.route('admin.class-type.edit').'" class="d-inline">', $row);
        $this->assertStringContainsString('<input type="hidden" name="typeID" value="'.$type->id.'">', $row);
        $this->assertStringContainsString('action="'.route('admin.class-type.destroy').'" data-confirm="'.e('Delete course '.$type->class_name.'? This cannot be undone.').'"', $row);
    }

    public function test_list_has_an_add_action(): void
    {
        $this->asRole('head')->get(route('head.class-type.index'))->assertOk()
            ->assertSee('<h1 class="page-title">Courses</h1>', false)
            ->assertSee('href="'.route('head.class.course.create').'"', false);
    }

    public function test_empty_list_shows_the_empty_state(): void
    {
        DB::table('class_types')->delete();

        $this->asRole('admin')->get(route('admin.class-type.index'))->assertOk()
            ->assertSeeText('No courses yet')->assertDontSee('<table', false);
    }

    public function test_add_page_has_name_and_a_non_negative_price(): void
    {
        $this->asRole('admin')->get(route('admin.class.course.create'))->assertOk()
            ->assertSee('<h1 class="page-title">Add course</h1>', false)
            ->assertSee('<input type="text" id="field-inputName" name="inputName" value="" class="form-control" required', false)
            ->assertSee('<input type="number" id="field-inputPrice" name="inputPrice" value="" class="form-control" min="0" required', false);
    }

    public function test_update_page_shows_the_name_read_only_and_explains_where_the_price_applies(): void
    {
        $type = DB::table('class_types')->orderBy('id')->first();

        $this->asRole('head')->post(route('head.class-type.edit'), ['typeID' => $type->id])->assertOk()
            ->assertSee('<h1 class="page-title">Update course</h1>', false)
            ->assertSee('<p class="form-static">'.e($type->class_name).'</p>', false)
            ->assertDontSee('name="inputName"', false)
            ->assertSeeText('The new price applies to Unpaid transactions and to classes that are not frozen.')
            ->assertSee('name="inputPrice" value="'.$type->class_price.'" class="form-control" min="0" required', false);
    }

    public function test_posting_the_rendered_update_form_unchanged_keeps_the_course(): void
    {
        $typeId = DB::table('class_types')->orderBy('id')->value('id');
        $before = (array) DB::table('class_types')->where('id', $typeId)->first();

        $update = route('head.class-type.update');
        $html = $this->asRole('head')->post(route('head.class-type.edit'), ['typeID' => $typeId])->assertOk()->getContent();
        $this->post($update, $this->formFields($html, $update))
            ->assertSessionHasNoErrors()->assertSessionMissing('error')->assertRedirect();

        $after = (array) DB::table('class_types')->where('id', $typeId)->first();
        unset($before['updated_at'], $after['updated_at']);
        $this->assertEquals($before, $after);
    }
    public function test_posting_the_rendered_update_form_unchanged_keeps_hand_edited_class_and_unpaid_prices(): void
    {
        $typeId = DB::table('class_types')->orderBy('id')->value('id');
        $classId = DB::table('class_transactions')->where('class_type_id', $typeId)->where('is_freeze', 0)->orderBy('id')->value('id');
        $this->assertNotNull($classId, 'seed has no non-frozen class for the first course');
        DB::table('class_transactions')->where('id', $classId)->update(['class_transaction_price' => 123456]);
        $unpaid = DB::table('transactions')->insertGetId([
            'students_id' => DB::table('students')->value('id'), 'class_transactions_id' => $classId,
            'payment_status' => 'Unpaid', 'price' => 111, 'transaction_date' => now(),
        ]);

        $update = route('head.class-type.update');
        $html = $this->asRole('head')->post(route('head.class-type.edit'), ['typeID' => $typeId])->assertOk()->getContent();
        $this->post($update, $this->formFields($html, $update))->assertSessionHasNoErrors()->assertRedirect();

        $this->assertEquals(111, DB::table('transactions')->where('id', $unpaid)->value('price'));
        $this->assertEquals(123456, DB::table('class_transactions')->where('id', $classId)->value('class_transaction_price'));
    }

    public function test_a_plus_prefixed_unchanged_price_is_still_unchanged(): void
    {
        $typeId = DB::table('class_types')->orderBy('id')->value('id');
        $price = DB::table('class_types')->where('id', $typeId)->value('class_price');
        $classId = DB::table('class_transactions')->where('class_type_id', $typeId)->where('is_freeze', 0)->orderBy('id')->value('id');
        $unpaid = DB::table('transactions')->insertGetId([
            'students_id' => DB::table('students')->value('id'), 'class_transactions_id' => $classId,
            'payment_status' => 'Unpaid', 'price' => 111, 'transaction_date' => now(),
        ]);

        $this->asRole('head')->post(route('head.class-type.update'), ['typeID' => $typeId, 'inputPrice' => '+'.$price])
            ->assertSessionHasNoErrors()->assertRedirect();

        $this->assertEquals(111, DB::table('transactions')->where('id', $unpaid)->value('price'));
        $this->assertEquals($price, DB::table('class_types')->where('id', $typeId)->value('class_price'));
    }

    public function test_deleting_a_course_that_classes_use_is_refused_with_the_count(): void
    {
        $typeId = DB::table('class_types')->orderBy('id')->value('id');
        DB::table('class_transactions')->insert([
            'class_type_id' => $typeId, 'Status' => 'aktif', 'is_freeze' => 0,
            'class_transaction_price' => 100000, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $count = DB::table('class_transactions')->where('class_type_id', $typeId)->count();
        $this->assertGreaterThan(1, $count);
        $index = route('head.class-type.index');

        $this->asRole('head')->from($index)->post(route('head.class-type.destroy'), ['typeID' => $typeId])
            ->assertRedirect($index)
            ->assertSessionHas('error', "{$count} classes still use this course; delete or move them first.")
            ->assertSessionMissing('msg');

        $this->assertDatabaseHas('class_types', ['id' => $typeId]);
    }

    public function test_deleting_a_course_one_class_uses_says_one_class(): void
    {
        $typeId = DB::table('class_types')->insertGetId(['class_name' => 'One Class Course', 'class_price' => 100000]);
        DB::table('class_transactions')->insert([
            'class_type_id' => $typeId, 'Status' => 'aktif', 'is_freeze' => 1,
            'class_transaction_price' => 100000, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->asRole('admin')->post(route('admin.class-type.destroy'), ['typeID' => $typeId])
            ->assertSessionHas('error', '1 class still uses this course; delete or move them first.');

        $this->assertDatabaseHas('class_types', ['id' => $typeId]);
    }

    public function test_deleting_a_course_without_classes_deletes_it(): void
    {
        $typeId = DB::table('class_types')->insertGetId(['class_name' => 'Unused Course', 'class_price' => 100000]);

        $this->asRole('admin')->post(route('admin.class-type.destroy'), ['typeID' => $typeId])
            ->assertRedirect()->assertSessionHas('msg', 'Success Delete Course Data')->assertSessionMissing('error');

        $this->assertDatabaseMissing('class_types', ['id' => $typeId]);
    }
}

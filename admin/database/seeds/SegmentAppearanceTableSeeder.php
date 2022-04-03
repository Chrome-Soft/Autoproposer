<?php

use App\SegmentAppearanceTemplate;
use Illuminate\Database\Seeder;

class SegmentAppearanceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $templates = SegmentAppearanceTemplate::all();

        if ($templates->count() == 0) {
            $template = new SegmentAppearanceTemplate;
            $template->name = 'Angol turista';
            $template->photo_path = 'segment-appearance-templates/angol_turista.png';
            $template->css_template = 'angol_turista.css';
            $template->save();

            $template = new SegmentAppearanceTemplate;
            $template->name = 'Hipster';
            $template->photo_path = 'segment-appearance-templates/hipster.png';
            $template->css_template = 'hipster.css';
            $template->save();

            $template = new SegmentAppearanceTemplate;
            $template->name = 'Holland';
            $template->photo_path = 'segment-appearance-templates/holland.png';
            $template->css_template = 'holland.css';
            $template->save();

            $template = new SegmentAppearanceTemplate;
            $template->name = 'Jómódú sznob';
            $template->photo_path = 'segment-appearance-templates/jomodu_sznob.png';
            $template->css_template = 'jomodu_sznob.css';
            $template->save();

            $template = new SegmentAppearanceTemplate;
            $template->name = 'Nyugati értelmiségi';
            $template->photo_path = 'segment-appearance-templates/nyugati_ertelmisegi.png';
            $template->css_template = 'nyugati_ertelmisegi.css';
            $template->save();

            $template = new SegmentAppearanceTemplate;
            $template->name = 'Orosz gyógyfürdőturizmus';
            $template->photo_path = 'segment-appearance-templates/orosz_gyogyfurdoturizmus.png';
            $template->css_template = 'orosz_gyogyfurdoturizmus.css';
            $template->save();

            $template = new SegmentAppearanceTemplate;
            $template->name = 'Osztrák kozmetikai turista';
            $template->photo_path = 'segment-appearance-templates/osztrak_kozmetikai_turista.png';
            $template->css_template = 'osztrak_kozmetikai_turista.css';
            $template->save();
        }
    }
}

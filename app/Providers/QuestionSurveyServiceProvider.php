<?php

namespace Nh\Providers;

use Illuminate\Support\ServiceProvider;

class QuestionSurveyServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            \Nh\Repositories\QuestionSurveys\QuestionSurveyRepository::class,
            \Nh\Repositories\QuestionSurveys\DbQuestionSurveyRepository::class
        );
    }
}

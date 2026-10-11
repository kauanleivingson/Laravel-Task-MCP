<?php

namespace Tests\Feature;

use App\Filament\Resources\Tasks\Schemas\TaskForm;
use App\Filament\Resources\Tasks\TaskResource;
use Filament\Schemas\Schema;
use Filament\Support\Contracts\TranslatableContentDriver;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Livewire\Component;
use Tests\TestCase;

class TaskSchemaTest extends TestCase
{
    public function test_task_form_schema_builds(): void
    {
        $schema = Schema::make();

        $configuredSchema = TaskForm::configure($schema);

        $this->assertNotNull($configuredSchema);
        $this->assertNotEmpty($configuredSchema->getComponents());
    }

    public function test_task_table_has_columns(): void
    {
        $livewire = new class extends Component implements HasTable
        {
            use InteractsWithTable;

            public function getTableQuery(): Builder|Relation|null
            {
                return null;
            }

            public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
            {
                return null;
            }
        };

        $table = TaskResource::table(Table::make($livewire));

        $this->assertNotEmpty($table->getColumns());
    }
}

<?php

namespace App\Filament\Resources\Tasks\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TaskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->required()
                ->max(255),

            Textarea::make('description'),

            Select::make('priority')
                ->options([
                    'low' => 'Baixa',
                    'medium' => 'Média',
                    'high' => 'Alta',
                ])
                ->required()
                ->default('medium'),

            Select::make('status')
                ->options([
                    'pending' => 'Pendente',
                    'in_progress' => 'Em andamento',
                    'completed' => 'Concluída',
                ])
                ->required()
                ->default('pending'),

            DatePicker::make('due_date'),
        ]);
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormResource\Pages;
use App\Models\Form;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FormResource extends Resource
{
    protected static ?string $model = Form::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static string|\UnitEnum|null $navigationGroup = 'Forms';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Tabs::make('form_tabs')
                ->tabs([
                    Tab::make('Details')
                        ->schema([
                            Forms\Components\TextInput::make('title')
                                ->required()
                                ->maxLength(255)
                                ->columnSpanFull(),

                            Forms\Components\Textarea::make('description')
                                ->rows(4)
                                ->maxLength(65535)
                                ->columnSpanFull(),

                            Forms\Components\Select::make('language')
                                ->options([
                                    'id' => 'Bahasa Indonesia',
                                    'en' => 'English',
                                ])
                                ->default('id')
                                ->required(),

                            Forms\Components\Select::make('status')
                                ->options([
                                    'draft'     => 'Draft',
                                    'published' => 'Published',
                                    'archived'  => 'Archived',
                                ])
                                ->default('draft')
                                ->required(),

                            Forms\Components\Toggle::make('allow_edit_after_submit')
                                ->label('Allow respondents to edit after submit')
                                ->default(true),

                            Forms\Components\Select::make('divisions')
                                ->multiple()
                                ->relationship('divisions', 'name')
                                ->preload()
                                ->required()
                                ->columnSpanFull(),
                        ])
                        ->columns(2),

                    Tab::make('Questions')
                        ->schema([
                            Forms\Components\Repeater::make('questions')
                                ->relationship('questions')
                                ->schema([
                                    Forms\Components\Select::make('type')
                                        ->options([
                                            'text'     => 'Text',
                                            'textarea' => 'Textarea',
                                            'number'   => 'Number',
                                            'email'    => 'Email',
                                            'date'     => 'Date',
                                            'radio'    => 'Radio',
                                            'checkbox' => 'Checkbox',
                                            'select'   => 'Select',
                                            'file'     => 'File',
                                        ])
                                        ->required()
                                        ->live()
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('label')
                                        ->required()
                                        ->maxLength(255)
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('help_text')
                                        ->nullable()
                                        ->maxLength(500)
                                        ->columnSpan(1),

                                    Forms\Components\Toggle::make('is_required')
                                        ->label('Required')
                                        ->default(false)
                                        ->columnSpan(1),

                                    Forms\Components\Repeater::make('options')
                                        ->relationship('options')
                                        ->schema([
                                            Forms\Components\TextInput::make('label')
                                                ->required()
                                                ->maxLength(255)
                                                ->columnSpan(1),
                                            Forms\Components\TextInput::make('value')
                                                ->required()
                                                ->maxLength(255)
                                                ->columnSpan(1),
                                        ])
                                        ->columns(2)
                                        ->defaultItems(1)
                                        ->addActionLabel('Add Option')
                                        ->reorderable()
                                        ->orderColumn('order')
                                        ->columnSpanFull()
                                        ->visible(fn (Get $get): bool =>
                                            in_array($get('type'), ['radio', 'checkbox', 'select'])
                                        ),
                                ])
                                ->columns(2)
                                ->defaultItems(1)
                                ->addActionLabel('Add Question')
                                ->reorderable()
                                ->orderColumn('order')
                                ->itemLabel(fn (array $state): ?string =>
                                    ($state['label'] ?? null)
                                        ? '[' . strtoupper($state['type'] ?? '?') . '] ' . $state['label']
                                        : null
                                )
                                ->collapsible()
                                ->collapsed()
                                ->cloneable()
                                ->columnSpanFull(),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('language')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'id' => 'ID',
                        'en' => 'EN',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'archived'  => 'danger',
                        default     => 'gray',
                    }),

                Tables\Columns\TextColumn::make('questions_count')
                    ->counts('questions')
                    ->label('Questions'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft'     => 'Draft',
                        'published' => 'Published',
                        'archived'  => 'Archived',
                    ]),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListForms::route('/'),
            'create' => Pages\CreateForm::route('/create'),
            'edit'   => Pages\EditForm::route('/{record}/edit'),
            'import' => Pages\ImportForm::route('/import'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withTrashed();
    }
}

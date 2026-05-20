<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormResource\Pages;
use App\Models\Form;
use App\Models\FormExportTemplate;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
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
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav_form_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav_forms');
    }

    public static function getModelLabel(): string
    {
        return __('admin.model_form');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Tabs::make('form_tabs')
                ->tabs([
                    Tab::make(__('admin.tab_details'))
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
                                ->options(fn () => [
                                    'draft'     => __('admin.status_draft'),
                                    'published' => __('admin.status_published'),
                                    'archived'  => __('admin.status_archived'),
                                ])
                                ->default('draft')
                                ->required(),

                            Forms\Components\Toggle::make('allow_edit_after_submit')
                                ->label(fn () => __('admin.field_allow_edit_after_submit'))
                                ->default(true),

                            Forms\Components\Select::make('divisions')
                                ->multiple()
                                ->relationship('divisions', 'name')
                                ->preload()
                                ->required()
                                ->columnSpanFull(),
                        ])
                        ->columns(2),

                    Tab::make(__('admin.tab_questions'))
                        ->schema([
                            Forms\Components\Repeater::make('questions')
                                ->relationship('questions')
                                ->schema([
                                    Forms\Components\Select::make('type')
                                        ->options(fn () => [
                                            'text'     => __('admin.qtype_text'),
                                            'textarea' => __('admin.qtype_textarea'),
                                            'number'   => __('admin.qtype_number'),
                                            'email'    => __('admin.qtype_email'),
                                            'date'     => __('admin.qtype_date'),
                                            'radio'    => __('admin.qtype_radio'),
                                            'checkbox' => __('admin.qtype_checkbox'),
                                            'select'   => __('admin.qtype_select'),
                                            'file'     => __('admin.qtype_file'),
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
                                        ->label(fn () => __('admin.col_required'))
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
                                        ->addActionLabel(fn () => __('admin.add_option'))
                                        ->reorderable()
                                        ->orderColumn('order')
                                        ->columnSpanFull()
                                        ->visible(fn (Get $get): bool =>
                                            in_array($get('type'), ['radio', 'checkbox', 'select'])
                                        ),
                                ])
                                ->columns(2)
                                ->defaultItems(1)
                                ->addActionLabel(fn () => __('admin.add_question'))
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

                    Tab::make(__('admin.tab_export_template'))
                        ->schema([
                            Section::make()
                                ->relationship('exportTemplate')
                                ->schema([

                                    Forms\Components\TextInput::make('title_text')
                                        ->label(fn () => __('admin.field_report_title'))
                                        ->placeholder('e.g. LAPORAN DATA PENDAMPINGAN 2026')
                                        ->maxLength(255)
                                        ->columnSpanFull(),

                                    Forms\Components\TextInput::make('subtitle_template')
                                        ->label(fn () => __('admin.field_subtitle_template'))
                                        ->placeholder('e.g. BULAN : {period_label}')
                                        ->helperText('Use {period_label} as a placeholder — it will be replaced by the release set\'s period label.')
                                        ->maxLength(255)
                                        ->columnSpanFull(),

                                    Forms\Components\Toggle::make('show_auto_number')
                                        ->label(fn () => __('admin.field_show_auto_number'))
                                        ->live()
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('auto_number_label')
                                        ->label(fn () => __('admin.field_auto_number_label'))
                                        ->default('NO')
                                        ->maxLength(50)
                                        ->columnSpan(1)
                                        ->visible(fn (Get $get): bool => (bool) $get('show_auto_number')),

                                    Forms\Components\Repeater::make('participant_columns')
                                        ->label(fn () => __('admin.field_participant_columns'))
                                        ->helperText('Choose which participant fields appear in the export and how they are labelled.')
                                        ->schema([
                                            Forms\Components\Select::make('field')
                                                ->label(fn () => __('admin.field_column_field'))
                                                ->options(fn () => [
                                                    'name'         => __('admin.pcol_name'),
                                                    'nip'          => __('admin.pcol_nip'),
                                                    'division'     => __('admin.pcol_division'),
                                                    'position'     => __('admin.pcol_position'),
                                                    'email'        => __('admin.pcol_email'),
                                                    'phone'        => __('admin.pcol_phone'),
                                                    'identifier'   => __('admin.pcol_identifier'),
                                                    'status'       => __('admin.pcol_status'),
                                                    'submitted_at' => __('admin.pcol_submitted_at'),
                                                    'updated_at'   => __('admin.pcol_updated_at'),
                                                ])
                                                ->required()
                                                ->columnSpan(1),

                                            Forms\Components\TextInput::make('label')
                                                ->label(fn () => __('admin.field_column_label'))
                                                ->required()
                                                ->maxLength(100)
                                                ->columnSpan(1),

                                            Forms\Components\Toggle::make('enabled')
                                                ->label(fn () => __('admin.field_column_show'))
                                                ->default(true)
                                                ->columnSpan(1),
                                        ])
                                        ->columns(3)
                                        ->default(FormExportTemplate::defaultParticipantColumns())
                                        ->reorderable()
                                        ->addActionLabel(fn () => __('admin.add_column'))
                                        ->columnSpanFull(),

                                    Forms\Components\TextInput::make('signature_role')
                                        ->label(fn () => __('admin.field_signature_role'))
                                        ->placeholder('e.g. Katimker Kabupaten Jembrana')
                                        ->maxLength(255)
                                        ->columnSpanFull(),

                                    Forms\Components\TextInput::make('signature_name')
                                        ->label(fn () => __('admin.field_signature_name'))
                                        ->maxLength(255)
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('signature_nip')
                                        ->label(fn () => __('admin.field_signature_nip'))
                                        ->maxLength(50)
                                        ->columnSpan(1),

                                    Forms\Components\Select::make('signature_position')
                                        ->label(fn () => __('admin.field_signature_position'))
                                        ->helperText('Where the signature block is placed horizontally on the exported sheet.')
                                        ->options(fn () => [
                                            'left'   => __('admin.sig_pos_left'),
                                            'center' => __('admin.sig_pos_center'),
                                            'right'  => __('admin.sig_pos_right'),
                                        ])
                                        ->default('right')
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),
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
                    ->label(__('admin.col_title'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('language')
                    ->label(__('admin.col_language'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'id' => 'ID',
                        'en' => 'EN',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('admin.col_status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'archived'  => 'danger',
                        default     => 'gray',
                    }),

                Tables\Columns\TextColumn::make('questions_count')
                    ->counts('questions')
                    ->label(__('admin.col_questions')),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(fn () => [
                        'draft'     => __('admin.status_draft'),
                        'published' => __('admin.status_published'),
                        'archived'  => __('admin.status_archived'),
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

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\Markdown;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make('Product Information')->schema([
                        TextInput::make('name')->required()->maxLength(255)->live(onBlur:true)
                        ->afterStateUpdated(fn(string $operation, $state, Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                        TextInput::make('slug')->maxLength(255)->disabled()->required()->dehydrated()
                        ->unique(Product::class, 'slug', ignoreRecord: true),

                        Textarea::make('description')->columnSpanFull()->rows(12)->required()
                    ])->columns(2),

                    Section::make('Images')->schema([
                        FileUpload::make('images')->multiple()->directory('products')->maxFiles(5)->reorderable()

                    ])
                ])->columnSpan(2),

                Group::make()->schema([
                    Section::make('Price')->schema([
                        TextInput::make('price')->numeric()->required()->prefix('$')
                    ]),
                    Section::make('Stock')->schema([
                        TextInput::make('stock')->numeric()->required()
                    ]),
                    Section::make('Associations')->schema([
                        Select::make('category_id')->required()->searchable()->preload()->relationship('category', 'name'),
                        Select::make('brand_id')->required()->preload()->searchable()->relationship('brand', 'name')
                    ]),
                    Section::make('Status')->schema([
                        Toggle::make('active')->required()->default(true),
                        Toggle::make('featured')->required(),
                        Toggle::make('on_sale')->required()
                    ])
                ])

            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('category.name')->searchable()->sortable(),
                TextColumn::make('brand.name')->searchable()->sortable(),
                TextColumn::make('slug')->searchable()->sortable(),
                TextColumn::make('price')->money('USD')->sortable(),
                TextColumn::make('stock')->sortable(),
                IconColumn::make('featured')->boolean(),
                IconColumn::make('on_sale')->boolean(),
                IconColumn::make('active')->boolean(),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault:true),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault:true),
                ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),

                SelectFilter::make('category')->relationship('category', 'name'),
                SelectFilter::make('brand')->relationship('brand', 'name')

            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

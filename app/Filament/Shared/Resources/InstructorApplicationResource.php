<?php

namespace App\Filament\Shared\Resources;

use App\Filament\Shared\Resources\InstructorApplicationResource\Pages;
use App\Models\InstructorProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Mail;
use App\Mail\InstructorApproved;
use App\Mail\InstructorRejected;

class InstructorApplicationResource extends Resource
{
    protected static ?string $model = InstructorProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationLabel = 'Instructor Applications';

    public static function form(Form $form): Form
    {
        // We don't need a form for creating/editing from admin panel for now
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Applicant Name')
                    ->searchable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('headline')
                    ->label('Profession'),
                TextColumn::make('website_url')
                    ->label('Portfolio')
                    ->url(fn(InstructorProfile $record): string => $record->website_url)
                    ->openUrlInNewTab(),
                TextColumn::make('application_status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    }),
            ])
            ->filters([
                SelectFilter::make('application_status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
            ])
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->action(function (InstructorProfile $record) {
                        $record->update(['application_status' => 'approved']);

                        /** @var \App\Models\User $user */
                        $user = $record->user;

                        Mail::to($user->email)->send(new InstructorApproved($user));
                    })
                    ->visible(fn(InstructorProfile $record): bool => $record->application_status === 'pending'),

                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->action(function (InstructorProfile $record) {
                        $record->update(['application_status' => 'rejected']);

                        /** @var \App\Models\User $user */
                        $user = $record->user;

                        Mail::to($user->email)->send(new InstructorRejected($user));
                    })
                    ->visible(fn(InstructorProfile $record): bool => $record->application_status === 'pending'),
            ])
            ->bulkActions([
                //
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
            'index' => Pages\ListInstructorApplications::route('/'),
        ];
    }

    // public static function canCreate(): bool
    // {
    //     return false;
    // }
}

<div class="flex flex-wrap items-center gap-2">
<button type="button" wire:click="edit({{ $user->id }})" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Edit</button>
@if($user->hasRole('client'))
    @if($user->access_status !== 'approved')<button type="button" wire:click="setAccess({{ $user->id }}, 'approved')" wire:confirm="Have you confirmed this person's membership with a known company contact?" class="rounded-lg bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700">Approve</button>@endif
    @if($user->access_status === 'pending')<button type="button" wire:click="setAccess({{ $user->id }}, 'rejected')" wire:confirm="Reject this registration?" class="rounded-lg bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700">Reject</button>@endif
    @if($user->access_status === 'approved')<button type="button" wire:click="setAccess({{ $user->id }}, 'suspended')" wire:confirm="Suspend this account?" class="rounded-lg bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700">Suspend</button>@endif
@elseif(auth()->user()->hasRole('super-admin') && (int)auth()->id() !== (int)$user->id)
    <button type="button" wire:click="setAccess({{ $user->id }}, '{{ $user->access_status === 'approved' ? 'suspended' : 'approved' }}')" wire:confirm="Change this staff account's access?" class="rounded-lg bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700">{{ $user->access_status === 'approved' ? 'Suspend' : 'Activate' }}</button>
@endif
@if((int)auth()->id() !== (int)$user->id)<button type="button" wire:click="delete({{ $user->id }})" wire:confirm="Delete this user?" class="rounded-lg px-2 py-2 text-xs font-medium text-rose-600 hover:bg-rose-50">Delete</button>@endif
</div>

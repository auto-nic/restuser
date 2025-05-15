<div class="py-12">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <p>
                    Du har tillgång till den här appen från flera organisationer. 
                    Vänligen välj vilken organisation du vill använda appen via:
                </p>

                <br>

                @foreach(auth()->tokens() as $key => $token)
                <div wire:click="selectToken({{ $key }})" class="rounded-md bg-green-50 border border-green-200 hover:cursor-pointer hover:bg-green-100 px-3 py-2 mb-2">{{ $token->customer_name }}</div>
                @endforeach
            </div>
        </div>
    </div>
</div>
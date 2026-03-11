<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diamond Brides Catalog</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .hide-scroll::-webkit-scrollbar { display: none; }
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen pb-10">

    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-md mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-xl font-serif font-bold text-gray-800 tracking-wider">DIAMOND BRIDES 💎</h1>
            <a href="/catalog" class="text-2xl hover:opacity-80 transition" title="Switch to Russian">🇷🇺</a>
        </div>
    </header>

    <main class="max-w-md mx-auto px-4 mt-6 space-y-8">
        @forelse ($brides as $bride)
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
                
                <div class="relative h-96 w-full flex overflow-x-auto snap-x snap-mandatory hide-scroll">
                    @if($bride->photos && count($bride->photos) > 0)
                        @foreach($bride->photos as $photoId)
                            <div class="snap-center shrink-0 w-full h-full">
                                <img src="{{ route('photo', ['fileId' => $photoId]) }}" class="w-full h-full object-cover">
                            </div>
                        @endforeach
                    @else
                        <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                            <span class="text-gray-400">No photos</span>
                        </div>
                    @endif
                </div>

                <div class="p-6">
                    <div class="flex justify-between items-end border-b border-gray-100 pb-4 mb-4">
                        <div>
                            <h2 class="text-3xl font-bold text-gray-800">{{ $bride->name }}, {{ $bride->age }}</h2>
                            <p class="text-gray-500 mt-1 flex items-center gap-1">
                                📍 {{ $bride->en_text['location'] ?? $bride->location }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-400">Height</p>
                            <p class="text-lg font-semibold text-gray-700">{{ $bride->height }} cm</p>
                        </div>
                    </div>

                    <div class="space-y-3 text-gray-700">
                        <div class="flex flex-wrap gap-2 mb-4">
                            <span class="px-3 py-1 bg-pink-50 text-pink-600 rounded-full text-sm font-medium">
                                💼 {{ $bride->en_text['occupation'] ?? $bride->occupation }}
                            </span>
                            <span class="px-3 py-1 bg-purple-50 text-purple-600 rounded-full text-sm font-medium">
                                🎨 {{ $bride->en_text['hobbies'] ?? $bride->hobbies }}
                            </span>
                            <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-sm font-medium">
                                👶 {{ $bride->has_kids ? 'Has kids' : 'No kids' }} (Wants: {{ mb_strtolower($bride->en_text['wants_kids'] ?? $bride->wants_kids) }})
                            </span>
                        </div>

                        <div>
                            <h3 class="text-xs uppercase tracking-widest text-gray-400 font-bold mb-1">About me</h3>
                            <p class="text-sm leading-relaxed">{{ $bride->en_text['about_me'] ?? $bride->about_me }}</p>
                        </div>

                        <div class="pt-2">
                            <h3 class="text-xs uppercase tracking-widest text-gray-400 font-bold mb-1">Perfect man</h3>
                            <p class="text-sm leading-relaxed italic text-gray-600">«{{ $bride->en_text['man_qualities'] ?? $bride->man_qualities }}»</p>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-gray-500 mt-20">
                <p class="text-2xl mb-2">🤷‍♀️</p>
                <p>No profiles available yet.</p>
            </div>
        @endforelse
    </main>
</body>
</html>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Эксклюзивный каталог</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Скрываем скроллбар для карусели фото, но оставляем возможность скроллить */
        .hide-scroll::-webkit-scrollbar { display: none; }
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen pb-10">

    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-md mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-xl font-serif font-bold text-gray-800 tracking-wider">DIAMOND BRIDES 💎</h1>
            <a href="/catalog/en" class="text-2xl hover:opacity-80 transition" title="Switch to English">🇬🇧</a>
        </div>
    </header>

    <main class="max-w-md mx-auto px-4 mt-6 space-y-8">
        
        @forelse ($brides as $bride)
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
                
                <div class="relative h-96 w-full flex overflow-x-auto snap-x snap-mandatory hide-scroll">
                    @if($bride->photos && count($bride->photos) > 0)
                        @foreach($bride->photos as $photoId)
                            <div class="snap-center shrink-0 w-full h-full">
                                <img src="{{ route('photo', ['fileId' => $photoId]) }}" 
                                     alt="Фото {{ $bride->name }}" 
                                     class="w-full h-full object-cover">
                            </div>
                        @endforeach
                    @else
                        <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                            <span class="text-gray-400">Нет фото</span>
                        </div>
                    @endif
                    
                    @if($bride->photos && count($bride->photos) > 1)
                        <div class="absolute bottom-3 right-3 bg-black/50 text-white text-xs px-2 py-1 rounded-full backdrop-blur-sm">
                            1 / {{ count($bride->photos) }} ➡
                        </div>
                    @endif
                </div>

                <div class="p-6">
                    <div class="flex justify-between items-end border-b border-gray-100 pb-4 mb-4">
                        <div>
                            <h2 class="text-3xl font-bold text-gray-800">{{ $bride->name }}, {{ $bride->age }}</h2>
                            <p class="text-gray-500 mt-1 flex items-center gap-1">
                                📍 {{ $bride->location }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-400">Рост</p>
                            <p class="text-lg font-semibold text-gray-700">{{ $bride->height }} см</p>
                        </div>
                    </div>

                    <div class="space-y-3 text-gray-700">
                        <div class="flex flex-wrap gap-2 mb-4">
                            <span class="px-3 py-1 bg-pink-50 text-pink-600 rounded-full text-sm font-medium">
                                💼 {{ $bride->occupation }}
                            </span>
                            <span class="px-3 py-1 bg-purple-50 text-purple-600 rounded-full text-sm font-medium">
                                🎨 {{ $bride->hobbies }}
                            </span>
                            <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-sm font-medium">
                                👶 {{ $bride->has_kids ? 'Есть дети' : 'Нет детей' }} (Хочет: {{ mb_strtolower($bride->wants_kids) }})
                            </span>
                        </div>

                        <div>
                            <h3 class="text-xs uppercase tracking-widest text-gray-400 font-bold mb-1">О себе</h3>
                            <p class="text-sm leading-relaxed">{{ $bride->about_me }}</p>
                        </div>

                        <div class="pt-2">
                            <h3 class="text-xs uppercase tracking-widest text-gray-400 font-bold mb-1">Идеальный мужчина</h3>
                            <p class="text-sm leading-relaxed italic text-gray-600">«{{ $bride->man_qualities }}»</p>
                        </div>
                    </div>
                    
                    @if($bride->en_text && is_array($bride->en_text))
                    <details class="mt-4 pt-4 border-t border-gray-100 cursor-pointer group">
                        <summary class="text-sm text-indigo-500 font-medium outline-none">Show English Translation 🇬🇧</summary>
                        <div class="mt-3 text-sm text-gray-600 space-y-1">
                            <p><strong>Location:</strong> {{ $bride->en_text['location'] ?? '' }}</p>
                            <p><strong>Occupation:</strong> {{ $bride->en_text['occupation'] ?? '' }}</p>
                            <p><strong>Hobbies:</strong> {{ $bride->en_text['hobbies'] ?? '' }}</p>
                            <p><strong>Wants kids:</strong> {{ mb_strtolower($bride->en_text['wants_kids'] ?? '') }}</p>
                            <p><strong>About me:</strong> {{ $bride->en_text['about_me'] ?? '' }}</p>
                            <p><strong>Perfect man:</strong> {{ $bride->en_text['man_qualities'] ?? '' }}</p>
                        </div>
                    </details>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center text-gray-500 mt-20">
                <p class="text-2xl mb-2">🤷‍♀️</p>
                <p>Анкет пока нет. Ждем первых красавиц!</p>
            </div>
        @endforelse

    </main>

    <footer class="text-center py-8 text-gray-400 text-sm mt-10">
        &copy; {{ date('Y') }} Diamond Brides Agency
    </footer>

</body>
</html>
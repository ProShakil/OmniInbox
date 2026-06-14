<script setup>
import { Head, usePage, router } from '@inertiajs/vue3'
import { ref, computed, nextTick, watch } from 'vue'

const page = usePage()
const user = page.props.auth.user

const logout = () => {
    router.post(route('logout'))
}

// State
const users = [
    { id: 1, name: 'John Doe', status: 'online', lastSeen: 'Online', lastMessage: 'Hey there! 👋', time: '10:30 AM' },
    { id: 2, name: 'Sarah Khan', status: 'offline', lastSeen: 'Last seen 5 min ago', lastMessage: 'See you tomorrow', time: '9:45 AM' },
    { id: 3, name: 'Alex Smith', status: 'online', lastSeen: 'Online', lastMessage: 'Great job! 🔥', time: 'Yesterday' },
    { id: 4, name: 'Emma Watson', status: 'offline', lastSeen: 'Last seen 1 hour ago', lastMessage: 'Thanks for the update', time: 'Yesterday' },
    { id: 5, name: 'Michael Lee', status: 'online', lastSeen: 'Online', lastMessage: 'Call me when free', time: 'Yesterday' },
]

const selectedUser = ref(null)
const messages = ref({}) // Store messages per user
const newMessageText = ref('')
const searchQuery = ref('')
const isChatOpen = ref(false) // For mobile: shows chat or user list
const messagesContainer = ref(null)
const isDesktop = ref(window.innerWidth >= 768)

// Initialize messages for each user
users.forEach(u => {
    if (!messages.value[u.id]) {
        messages.value[u.id] = [
            { id: 1, from: u.name, text: `Hi, I am ${u.name} 👋`, time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) },
            { id: 2, from: 'me', text: 'Nice to meet you!', time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) },
        ]
    }
})

// Computed
const filteredUsers = computed(() => {
    if (!searchQuery.value) return users
    return users.filter(u => u.name.toLowerCase().includes(searchQuery.value.toLowerCase()))
})

const currentMessages = computed(() => {
    if (!selectedUser.value) return []
    return messages.value[selectedUser.value.id] || []
})

// Watch for window resize
const handleResize = () => {
    isDesktop.value = window.innerWidth >= 768
    if (isDesktop.value) {
        isChatOpen.value = false // Reset mobile state when desktop
    }
}

window.addEventListener('resize', handleResize)

// Methods
const selectUser = (u) => {
    selectedUser.value = u
    
    // On mobile: hide user list, show chat
    if (!isDesktop.value) {
        isChatOpen.value = true
    }
    
    // Scroll to bottom of messages
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
        }
    })
}

const goBackToUserList = () => {
    isChatOpen.value = false
    selectedUser.value = null
}

const sendMessage = () => {
    if (!newMessageText.value.trim() || !selectedUser.value) return
    
    const newMsg = {
        id: Date.now(),
        from: 'me',
        text: newMessageText.value,
        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    }
    
    messages.value[selectedUser.value.id].push(newMsg)
    newMessageText.value = ''
    
    // Scroll to bottom
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
        }
    })
}

const handleKeyPress = (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault()
        sendMessage()
    }
}

const getInitials = (name) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

// Set initial selected user for desktop
if (isDesktop.value && users.length > 0) {
    selectedUser.value = users[0]
}
</script>

<template>
    <Head title="Dashboard" />

    <div class="h-screen flex flex-col bg-gray-100">
        
        <!-- TOP BAR -->
        <div class="flex items-center justify-between px-4 py-3 bg-white border-b shadow-sm flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="h-12 w-12 rounded-2xl bg-gradient-to-r from-blue-600 to-cyan-500 flex items-center justify-center">
                    <i class="fa-solid fa-comments text-white"></i>
                </div>
                <div class="font-semibold text-gray-800">
                    OmniInbox
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="text-sm text-gray-700 font-medium hidden sm:block">
                    {{ user.name }}
                </div>
                <div class="h-8 w-8 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center text-grey text-sm font-semibold">
                    {{ user.name }}
                </div>
                <button
                    @click="logout"
                    class="text-sm px-3 py-1.5 rounded-full bg-red-50 text-red-600 hover:bg-red-100 transition-colors font-medium"
                >
                    Logout
                </button>
            </div>
        </div>

        <!-- MAIN CONTENT -->
        <div class="flex flex-1 overflow-hidden bg-white">
            
            <!-- DESKTOP LAYOUT: Sidebar always visible + Chat always visible -->
            <template v-if="isDesktop">
                <!-- User List Sidebar -->
                <div class="w-[320px] lg:w-[360px] border-r flex flex-col bg-white">
                    <!-- Search -->
                    <div class="p-3 border-b">
                        <div class="relative">
                            <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search chat"
                                class="w-full rounded-full bg-gray-100 !pl-10 !pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                style="padding-left: 2.5rem !important; padding-right: 1rem !important;"
                            />
                        </div>
                    </div>

                    <!-- Users -->
                    <div class="flex-1 overflow-y-auto custom-scroll">
                        <div v-for="u in filteredUsers" :key="u.id">
                            <div
                                @click="selectUser(u)"
                                class="flex items-center gap-3 p-3 mx-1 my-1 rounded-xl cursor-pointer transition-all duration-200"
                                :class="selectedUser?.id === u.id ? 'bg-blue-100 border-l-4 border-blue-500' : 'hover:bg-gray-50'"
                            >
                                <div class="relative flex-shrink-0">
                                    <div class="h-12 w-12 rounded-full bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 flex items-center justify-center text-gray-700 font-semibold">
                                        {{ getInitials(u.name) }}
                                    </div>
                                    <span
                                        v-if="u.status === 'online'"
                                        class="absolute bottom-0 right-0 h-3 w-3 rounded-full bg-green-500 border-2 border-white"
                                    ></span>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm font-semibold text-gray-900 truncate">
                                            {{ u.name }}
                                        </div>
                                        <div class="text-xs text-gray-400">{{ u.time }}</div>
                                    </div>
                                    <div class="text-xs text-gray-500 truncate">
                                        {{ u.lastMessage }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chat Area (Desktop) -->
                <div class="flex-1 flex flex-col bg-gray-50">
                    <div v-if="selectedUser" class="flex flex-col h-full">
                        <!-- Chat Header -->
                        <div class="flex items-center gap-3 px-4 py-3 border-b bg-white">
                            <div class="relative">
                                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-black font-semibold">
                                    {{ getInitials(selectedUser.name) }}
                                </div>
                                <span
                                    v-if="selectedUser.status === 'online'"
                                    class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full bg-green-500 border-2 border-white"
                                ></span>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-gray-900">{{ selectedUser.name }}</div>
                                <div class="text-xs" :class="selectedUser.status === 'online' ? 'text-green-500' : 'text-gray-400'">
                                    {{ selectedUser.status === 'online' ? 'Online' : 'Offline' }}
                                </div>
                            </div>
                        </div>

                        <!-- Messages -->
                        <div ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-3 custom-scroll bg-gradient-to-b from-gray-50 to-gray-100">
                            <div
                                v-for="m in currentMessages"
                                :key="m.id"
                                class="flex"
                                :class="m.from === 'me' ? 'justify-end' : 'justify-start'"
                            >
                                <div class="flex items-end gap-1 max-w-[70%]">
                                    <div
                                        class="px-4 py-2 rounded-2xl text-sm"
                                        :class="m.from === 'me'
                                            ? 'bg-blue-500 text-white rounded-br-md'
                                            : 'bg-white text-gray-800 rounded-bl-md shadow-sm'"
                                    >
                                        {{ m.text }}
                                    </div>
                                    <div class="text-xs text-gray-400">{{ m.time }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Input -->
                        <div class="p-3 bg-white border-t flex items-center gap-2">
                            <input
                                v-model="newMessageText"
                                @keypress="handleKeyPress"
                                type="text"
                                placeholder="Type a message..."
                                class="flex-1 rounded-full bg-gray-100 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                            />
                            <button
                                @click="sendMessage"
                                :disabled="!newMessageText.trim()"
                                class="rounded-full bg-blue-500 text-white px-5 py-2.5 text-sm hover:bg-blue-600 transition disabled:opacity-50"
                            >
                                Send
                            </button>
                        </div>
                    </div>
                    
                    <div v-else class="flex items-center justify-center h-full text-gray-400">
                        <div class="text-center">
                            <i class="fa-regular fa-comment-dots text-5xl mb-3"></i>
                            <p>Select a chat to start messaging</p>
                        </div>
                    </div>
                </div>
            </template>

            <!-- MOBILE LAYOUT: Either show user list OR chat, not both -->
            <template v-else>
                <!-- User List (shown when chat is closed) -->
                <div v-if="!isChatOpen" class="w-full h-full flex flex-col bg-white">
                    <!-- Search -->
                    <div class="p-3 border-b">
                        <div class="relative">
                            <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search or start new chat"
                                class="w-full rounded-full bg-gray-100 pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                            />
                        </div>
                    </div>

                    <!-- Users List Full Screen -->
                    <div class="flex-1 overflow-y-auto custom-scroll">
                        <div v-for="u in filteredUsers" :key="u.id">
                            <div
                                @click="selectUser(u)"
                                class="flex items-center gap-3 p-4 mx-2 my-1 rounded-xl active:bg-gray-100 cursor-pointer transition"
                            >
                                <div class="relative flex-shrink-0">
                                    <div class="h-14 w-14 rounded-fullbg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 flex items-center justify-center text-gray-700 font-semibold text-lg">
                                        {{ getInitials(u.name) }}
                                    </div>
                                    <span
                                        v-if="u.status === 'online'"
                                        class="absolute bottom-0 right-0 h-3.5 w-3.5 rounded-full bg-green-500 border-2 border-white"
                                    ></span>
                                </div>

                                <div class="flex-1 min-w-0 border-b pb-3" :class="!filteredUsers.length ? 'border-b-0' : ''">
                                    <div class="flex items-center justify-between">
                                        <div class="text-base font-semibold text-gray-900">
                                            {{ u.name }}
                                        </div>
                                        <div class="text-xs text-gray-400">{{ u.time }}</div>
                                    </div>
                                    <div class="text-sm text-gray-500 truncate">
                                        {{ u.lastMessage }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div v-if="filteredUsers.length === 0" class="flex flex-col items-center justify-center h-full text-gray-400">
                            <i class="fa-solid fa-user-slash text-5xl mb-3"></i>
                            <p class="text-sm">No users found</p>
                        </div>
                    </div>
                </div>

                <!-- Chat Area (shown when a user is selected) -->
                <div v-else class="w-full h-full flex flex-col bg-gray-50">
                    <!-- Chat Header with Back Button -->
                    <div class="flex items-center gap-3 px-3 py-2 border-b bg-white">
                        <button 
                            @click="goBackToUserList"
                            class="p-2 -ml-2 rounded-full hover:bg-gray-100 active:bg-gray-200"
                        >
                            <i class="fa-solid fa-arrow-left text-xl text-gray-700"></i>
                        </button>
                        
                        <div class="relative">
                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-semibold">
                                {{ getInitials(selectedUser.name) }}
                            </div>
                            <span
                                v-if="selectedUser.status === 'online'"
                                class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full bg-green-500 border-2 border-white"
                            ></span>
                        </div>
                        
                        <div class="flex-1">
                            <div class="text-sm font-semibold text-gray-900">{{ selectedUser.name }}</div>
                            <div class="text-xs" :class="selectedUser.status === 'online' ? 'text-green-500' : 'text-gray-400'">
                                {{ selectedUser.status === 'online' ? 'Online' : 'Offline' }}
                            </div>
                        </div>
                        
                        <button class="p-2 rounded-full hover:bg-gray-100">
                            <i class="fa-solid fa-phone text-gray-600"></i>
                        </button>
                        <button class="p-2 rounded-full hover:bg-gray-100">
                            <i class="fa-solid fa-video text-gray-600"></i>
                        </button>
                    </div>

                    <!-- Messages -->
                    <div ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-3 custom-scroll bg-gradient-to-b from-gray-50 to-gray-100">
                        <div
                            v-for="m in currentMessages"
                            :key="m.id"
                            class="flex"
                            :class="m.from === 'me' ? 'justify-end' : 'justify-start'"
                        >
                            <div class="flex items-end gap-1 max-w-[85%]">
                                <div
                                    class="px-4 py-2.5 rounded-2xl text-sm"
                                    :class="m.from === 'me'
                                        ? 'bg-blue-500 text-white rounded-br-md'
                                        : 'bg-white text-gray-800 rounded-bl-md shadow-sm'"
                                >
                                    {{ m.text }}
                                </div>
                                <div class="text-xs text-gray-400">{{ m.time }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Input -->
                    <div class="p-3 bg-white border-t flex items-center gap-2">
                        <button class="p-2 rounded-full hover:bg-gray-100">
                            <i class="fa-regular fa-face-smile text-gray-600 text-xl"></i>
                        </button>
                        <input
                            v-model="newMessageText"
                            @keypress="handleKeyPress"
                            type="text"
                            placeholder="Type a message..."
                            class="flex-1 rounded-full bg-gray-100 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                        />
                        <button
                            @click="sendMessage"
                            :disabled="!newMessageText.trim()"
                            class="rounded-full bg-blue-500 text-white p-2.5 px-5 text-sm hover:bg-blue-600 transition disabled:opacity-50"
                        >
                            Send
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<style scoped>
.custom-scroll::-webkit-scrollbar {
    width: 5px;
}

.custom-scroll::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.custom-scroll::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

.custom-scroll::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
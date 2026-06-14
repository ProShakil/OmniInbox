<script setup>
import { Head, usePage, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const page = usePage()
const user = page.props.auth.user

const logout = () => {
    router.post(route('logout'))
}

const users = [
    { id: 1, name: 'John Doe', status: 'online' },
    { id: 2, name: 'Sarah Khan', status: 'offline' },
    { id: 3, name: 'Alex Smith', status: 'online' },
]

const selectedUser = ref(users[0])

const messages = ref([
    { id: 1, from: 'John Doe', text: 'Hey 👋' },
    { id: 2, from: 'me', text: 'Hello! How are you?' },
])

const selectUser = (u) => {
    selectedUser.value = u

    // demo messages (later DB/real chat replace korte parba)
    messages.value = [
        { id: 1, from: u.name, text: `Hi, I am ${u.name} 👋` },
        { id: 2, from: 'me', text: 'Nice to meet you!' },
    ]
}
</script>

<template>
    <Head title="Dashboard" />

    <div class="h-screen flex flex-col bg-white">

        <!-- TOP BAR -->
        <div class="flex items-center justify-between border-b px-4 py-2 bg-white">
            <div class="flex items-center gap-3">
                <div class="h-12 w-12 rounded-2xl bg-gradient-to-r from-blue-600 to-cyan-500 flex items-center justify-center">
                    <i class="fa-solid fa-comments text-white"></i>
                </div>
                <div class="font-semibold text-gray-800">
                    OmniInbox
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="text-sm text-gray-600">
                    {{ user.name }}
                </div>

                <button
                    @click="logout"
                    class="text-sm px-3 py-1 rounded-full bg-red-50 text-red-600 hover:bg-red-100"
                >
                    Logout
                </button>
            </div>
        </div>

        <!-- MAIN -->
        <div class="flex flex-1 overflow-hidden">

            <!-- SIDEBAR -->
            <div class="w-[320px] border-r bg-white overflow-y-auto">

                <div class="p-3">
                    <input
                        type="text"
                        placeholder="Search Messenger"
                        class="w-full rounded-full bg-gray-100 px-4 py-2 text-sm focus:outline-none"
                    />
                </div>

                <div class="space-y-1 px-2">

                    <div
                        v-for="u in users"
                        :key="u.id"
                        @click="selectUser(u)"
                        class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-100 cursor-pointer"
                        :class="selectedUser.id === u.id ? 'bg-gray-100' : ''"
                    >

                        <div class="relative">
                            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                <i class="fa-solid fa-user text-gray-600"></i>
                            </div>

                            <span
                                v-if="u.status === 'online'"
                                class="absolute bottom-0 right-0 h-3 w-3 rounded-full bg-green-500 border-2 border-white"
                            ></span>
                        </div>

                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-900">
                                {{ u.name }}
                            </div>
                            <div class="text-xs text-gray-500">
                                Click to start chat
                            </div>
                        </div>

                    </div>

                </div>
            </div>

            <!-- CHAT AREA -->
            <div class="flex flex-col flex-1 bg-gray-50">

                <!-- HEADER -->
                <div class="flex items-center justify-between px-4 py-3 border-b bg-white">

                    <div class="flex items-center gap-3">
                        <div class="h-9 w-9 rounded-full bg-gray-300 flex items-center justify-center">
                            <i class="fa-solid fa-user text-gray-600"></i>
                        </div>

                        <div>
                            <div class="text-sm font-semibold text-gray-900">
                                {{ selectedUser.name }}
                            </div>

                            <div
                                class="text-xs"
                                :class="selectedUser.status === 'online' ? 'text-green-500' : 'text-gray-400'"
                            >
                                {{ selectedUser.status === 'online' ? 'Active now' : 'Offline' }}
                            </div>
                        </div>
                    </div>

                </div>

                <!-- MESSAGES -->
                <div class="flex-1 overflow-y-auto p-5 space-y-3">

                    <div
                        v-for="m in messages"
                        :key="m.id"
                        class="flex"
                        :class="m.from === 'me' ? 'justify-end' : 'justify-start'"
                    >
                        <div
                            class="max-w-[70%] px-4 py-2 rounded-2xl text-sm"
                            :class="m.from === 'me'
                                ? 'bg-blue-600 text-white'
                                : 'bg-white border text-gray-800'"
                        >
                            {{ m.text }}
                        </div>
                    </div>

                </div>

                <!-- INPUT -->
                <div class="p-3 bg-white border-t flex items-center gap-2">

                    <input
                        type="text"
                        placeholder="Type a message..."
                        class="flex-1 rounded-full bg-gray-100 px-4 py-2 text-sm focus:outline-none"
                    />

                    <button class="rounded-full bg-blue-600 text-white px-5 py-2 text-sm hover:bg-blue-700">
                        Send
                    </button>

                </div>

            </div>
        </div>
    </div>
</template>
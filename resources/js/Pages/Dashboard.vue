<script setup>
import { Head, usePage, router } from '@inertiajs/vue3'
import { ref, computed, nextTick, watch, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import 'emoji-picker-element'

const page = usePage()
const user = page.props.auth.user

const logout = () => {
    router.post(route('logout'))
}

// State
const newMessageText = ref('')
const searchQuery = ref('')
const isChatOpen = ref(false) // For mobile: shows chat or user list
const messagesContainer = ref(null)
const isDesktop = ref(window.innerWidth >= 768)
const users = ref([])
const pagination = ref(1)
const hasMore = ref(true)
const loading = ref(false)
const selectedUser = ref(null)
const messages = ref([])
const loadingMessages = ref(false)

const loadConversations = async () => {

    if (loading.value || !hasMore.value) return

    loading.value = true

    try {

        const response = await axios.get(
            route('admin.conversations', {
                page: pagination.value
            })
        )

        users.value.push(...response.data.data)

        hasMore.value =
            response.data.next_page_url !== null

        pagination.value++

    } catch (error) {

        console.error(error)

    } finally {

        loading.value = false
    }
}
onMounted(() => {
    loadConversations()
})


// Computed
const filteredUsers = computed(() => {
    if (!searchQuery.value) return users.value
    return users.value.filter(u =>
        (u.contact?.name || '')
            .toLowerCase()
            .includes(searchQuery.value.toLowerCase())
    )
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

const loadMessages = async (conversationId) => {

    try {

        const response = await axios.get(
            route('admin.messages', conversationId)
        )

        messages.value = response.data.messages

    } catch (error) {

        console.error(error)
    }
}


const selectUser = async  (u) => {
    selectedUser.value = u
    await loadMessages(u.id)
    console.log(selectedUser.value)
    
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

const handleKeyPress = (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault()
        sendMessage()
    }
}

const getInitials = (name) => {

    if (!name) return '?'

    return name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
}

// Set initial selected user for desktop

const messageInput = ref(null)

const autoResize = () => {
    const el = messageInput.value

    if (!el) return

    el.style.height = 'auto'

    const maxHeight = 192 // ~8 lines

    if (el.scrollHeight > maxHeight) {
        el.style.height = maxHeight + 'px'
        el.style.overflowY = 'auto'
    } else {
        el.style.height = el.scrollHeight + 'px'
        el.style.overflowY = 'hidden'
    }
}

// Time
const currentTime = ref('')
const currentDate = ref('')

let clockInterval = null

const updateClock = () => {
    const now = new Date()

    let hours = now.getHours()
    const minutes = String(now.getMinutes()).padStart(2, '0')
    const seconds = String(now.getSeconds()).padStart(2, '0')

    const ampm = hours >= 12 ? 'PM' : 'AM'

    hours = hours % 12
    hours = hours ? hours : 12

    // 09, 08, 07 format
    const formattedHours = String(hours).padStart(2, '0')

    currentTime.value = `${formattedHours}:${minutes}:${seconds} ${ampm}`

    currentDate.value = now.toLocaleDateString('en-US', {
        weekday: 'short',
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    })
}

onMounted(() => {
    updateClock()
    clockInterval = setInterval(updateClock, 1000)
})

onUnmounted(() => {
    clearInterval(clockInterval)
})

const timeAgo = (date) => {
    const now = new Date()
    const past = new Date(date)

    const seconds = Math.floor((now - past) / 1000)

    if (seconds < 60) {
        return 'Just now'
    }

    const minutes = Math.floor(seconds / 60)

    if (minutes < 60) {
        return `${minutes} min ago`
    }

    const hours = Math.floor(minutes / 60)

    if (hours < 24) {
        return `${hours} hour${hours > 1 ? 's' : ''} ago`
    }

    const days = Math.floor(hours / 24)

    if (days === 1) {
        return 'Yesterday'
    }

    if (days < 7) {
        return `${days} days ago`
    }

    const weeks = Math.floor(days / 7)

    if (weeks < 5) {
        return `${weeks} week${weeks > 1 ? 's' : ''} ago`
    }

    const months = Math.floor(days / 30)

    if (months < 12) {
        return `${months} month${months > 1 ? 's' : ''} ago`
    }

    const years = Math.floor(days / 365)

    return `${years} year${years > 1 ? 's' : ''} ago`
}

const isImage = (file) => {

    const ext = file.file_name.split('.').pop().toLowerCase()

    return [
        'jpg',
        'jpeg',
        'png',
        'gif',
        'webp',
        'svg'
    ].includes(ext)
}

const isAudio = (file) => {

    const ext = file.file_name.split('.').pop().toLowerCase()

    return [
        'mp3',
        'wav',
        'ogg',
        'm4a',
        'aac'
    ].includes(ext)
}

const isVideo = (file) => {

    const ext = file.file_name.split('.').pop().toLowerCase()

    return [
        'mp4',
        'webm',
        'mov',
        'avi',
        'mkv'
    ].includes(ext)
}

// Input Trigger
const fileInput = ref(null)
const selectedFiles = ref([])

const handleFiles = (e) => {
    selectedFiles.value = [...e.target.files]
}

// Emoji
const showEmojiPicker = ref(false)

const addEmoji = (emoji) => {
    newMessageText.value += emoji.detail.unicode
}

// Voice Recording
const isRecording = ref(false)
const recordingTime = ref(0)

let mediaRecorder = null
let audioChunks = []
let timer = null

const startRecording = async () => {

    const stream = await navigator.mediaDevices.getUserMedia({
        audio: true
    })

    mediaRecorder = new MediaRecorder(stream)

    audioChunks = []

    mediaRecorder.ondataavailable = (e) => {
        audioChunks.push(e.data)
    }

    mediaRecorder.start()

    isRecording.value = true

    recordingTime.value = 0

    timer = setInterval(() => {
        recordingTime.value++
    }, 1000)
}

const cancelRecording = () => {

    if (mediaRecorder) {

        mediaRecorder.stop()
    }

    clearInterval(timer)

    audioChunks = []

    recordingTime.value = 0

    isRecording.value = false
}
// Send

const sendRecording = async () => {

    const audioBlob = await stopRecording()

    const formData = new FormData()

    formData.append(
        'audio',
        audioBlob,
        'voice.webm'
    )

    formData.append(
        'conversation_id',
        selectedUser.value.id
    )

    await axios.post(
        route('chat.voice.store'),
        formData
    )

    await loadMessages(
        selectedUser.value.id
    )

    recordingTime.value = 0
}

// Normal Text

const sendMessage = async () => {

    const formData = new FormData()

    formData.append(
        'conversation_id',
        selectedUser.value.id
    )

    formData.append(
        'message',
        newMessageText.value
    )

    selectedFiles.value.forEach(file => {

        formData.append(
            'attachments[]',
            file
        )

    })

    await axios.post(
        route('chat.reply.store'),
        formData
    )

    newMessageText.value = ''

    selectedFiles.value = []

    await loadMessages(
        selectedUser.value.id
    )
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
                <div
                    class="hidden xl:flex items-center ml-4 px-4 py-2 rounded-xl bg-gradient-to-r from-slate-900 via-blue-900 to-slate-900 text-white shadow-lg"
                >
                    <div class="flex flex-col items-center">
                        <span
                            class="font-mono text-lg font-bold tracking-wider text-center w-[140px]"
                        >
                            {{ currentTime }}
                        </span>

                        <span
                            class="font-mono text-[11px] text-blue-200 text-center whitespace-nowrap"
                        >
                            {{ currentDate }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-600 via-indigo-500 to-sky-400 flex items-center justify-center text-white text-sm font-semibold shadow-lg ring-2 ring-white/20">
                    {{ getInitials(user.name) }}
                </div>
                <div class="text-sm text-gray-700 font-medium hidden sm:block">
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
                                    <div class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-600 via-indigo-500 to-sky-400 flex items-center justify-center text-white font-semibold">
                                        {{ getInitials(u.contact?.name) }}
                                    </div>
                                    <span                                        
                                        class="absolute bottom-0 right-0 h-3 w-3 rounded-full bg-green-500 border-2 border-white"
                                    ></span>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm font-semibold text-gray-900 truncate">
                                            {{ u.contact?.name }}
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div
                                                v-if="u.contact?.source === 'whatsapp'"
                                                class="h-6 w-6 rounded-full bg-green-100 flex items-center justify-center"
                                            >
                                                <i class="fa-brands fa-whatsapp text-green-600 text-sm"></i>
                                            </div>
        
                                            <div
                                                v-else-if="u.contact?.source === 'messenger'"
                                                class="h-6 w-6 rounded-full bg-blue-100 flex items-center justify-center"
                                            >
                                                <i class="fa-brands fa-facebook-messenger text-blue-600 text-sm"></i>
                                            </div>
        
                                            <div
                                                v-else
                                                class="h-6 w-6 rounded-full bg-slate-100 flex items-center justify-center"
                                            >
                                                <i class="fa-solid fa-globe text-slate-600 text-xs"></i>
                                            </div>
                                            <div class="text-xs text-gray-400"> {{ timeAgo(u.last_message_at) }}</div>
                                        </div>
                                    </div>
                                    <div class="text-xs text-gray-500 truncate">
                                        {{ u.latest_message?.message }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chat Area (Desktop) -->
                <div class="flex-1 flex flex-col bg-gray-50" style="background-image: url('/assets/images/chatbg.png'); background-repeat: repeat; background-size: 400px; background-color: #f8f9fb;">
                    <div v-if="selectedUser" class="flex flex-col h-full">
                        <!-- Chat Header -->
                        <div class="flex items-center gap-3 px-4 py-3 border-b bg-white">
                            <div class="relative">
                                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-600 via-indigo-500 to-sky-400 flex items-center justify-center text-white font-semibold">
                                    {{ getInitials(selectedUser.contact?.name) }}
                                </div>
                                <span                                    
                                    class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full bg-green-500 border-2 border-white"
                                ></span>
                            </div>                            
                            <div class="flex-1">
                                <div class="text-sm font-semibold text-gray-900">{{ selectedUser.contact?.name }}</div>
                                <div class="text-xs text-green-500">
                                    Online
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-1">
                                <button class="p-2 rounded-full hover:bg-gray-100"
                                    v-if="selectedUser.contact?.platform === 'whatsapp'"                                    
                                >
                                    <i class="fa-brands fa-whatsapp text-green-600"></i>
                                </button>

                                <button class="p-2 rounded-full hover:bg-gray-100"
                                    v-else-if="selectedUser.contact?.platform === 'messenger'"                                
                                >
                                    <i class="fa-brands fa-facebook-messenger text-blue-600"></i>
                                </button>

                                <button class="p-2 rounded-full hover:bg-gray-100"
                                    v-else                                    
                                >
                                    <i class="fa-solid fa-globe text-slate-600"></i>
                                </button>
                                <button class="p-2 rounded-full hover:bg-gray-100">
                                    <i class="fa-solid fa-phone text-gray-600"></i>
                                </button>
                                <button class="p-2 rounded-full hover:bg-gray-100">
                                    <i class="fa-solid fa-video text-gray-600"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Messages -->
                        <div ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-3 custom-scroll">
                            <div
                                v-for="m in messages"
                                :key="m.id"
                                class="flex"
                                :class="m.sender_type  === 'admin' ? 'justify-end' : 'justify-start'"
                            >
                                <div class=" max-w-[70%]">
                                    <div
                                        class="px-4 py-2 rounded-2xl text-sm"
                                        :class="m.sender_type === 'admin'
                                            ? 'bg-blue-500 text-white rounded-br-md'
                                            : 'bg-white text-gray-800 rounded-bl-md shadow-sm'"
                                    >
                                        {{ m.message }}
                                    </div>
                                    <div
                                        v-if="m.attachments?.length"
                                        class="mt-2 space-y-2"
                                    >
                                        <div
                                            v-for="file in m.attachments"
                                            :key="file.id"
                                        >

                                            <!-- Image -->
                                            <img
                                                v-if="isImage(file)"
                                                :src="`/${file.file_path}`"
                                                :alt="file.file_name"
                                                class="max-w-[250px] rounded-lg border"
                                            >

                                            <!-- Audio -->
                                            <audio
                                                v-else-if="isAudio(file)"
                                                controls
                                                class="w-full max-w-[280px]"
                                            >
                                                <source :src="`/${file.file_path}`">
                                            </audio>

                                            <!-- Video -->
                                            <video
                                                v-else-if="isVideo(file)"
                                                controls
                                                class="max-w-[300px] rounded-lg border"
                                            >
                                                <source :src="`/${file.file_path}`">
                                            </video>

                                            <!-- Other Files -->
                                            <a
                                                v-else
                                                :href="`/${file.file_path}`"
                                                target="_blank"
                                                class="flex items-center gap-2 p-2 bg-gray-100 rounded-lg hover:bg-gray-200"
                                            >
                                                <i class="fa-solid fa-file-lines text-gray-600"></i>

                                                <span class="text-xs truncate">
                                                    {{ file.file_name }}
                                                </span>

                                                <i class="fa-solid fa-download ml-auto"></i>
                                            </a>

                                        </div>
                                    </div>
                                    <div class="text-xs text-right text-gray-400">{{ timeAgo(m.created_at) }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Input -->
                        <div class="bg-white px-3 py-2 border border-gray-200 shadow-sm flex items-end gap-1.5 m-4 transition-all duration-200" :class="messageInput?.scrollHeight > 50 ? 'rounded-2xl' : 'rounded-full'"
                        >
                            <input
                                ref="fileInput"
                                type="file"
                                class="hidden"
                                multiple
                                @change="handleFiles"
                            />
                            <button
                                @click="fileInput.click()"
                                class="h-9 w-9 shrink-0 flex items-center justify-center rounded-full hover:bg-gray-100 transition text-gray-500"
                            >
                                <i class="fa-solid fa-paperclip text-lg text-gray-700"></i>
                            </button>

                            <button
                                @click="showEmojiPicker = !showEmojiPicker"
                                class="h-9 w-9 shrink-0 flex items-center justify-center rounded-full hover:bg-gray-100 transition text-gray-500"
                            >
                                <i class="fa-regular fa-face-smile text-xl text-gray-600"></i>
                            </button>
                            <div
                                v-if="showEmojiPicker"
                                class="absolute bottom-20 left-[380px] z-50"
                            >
                                <emoji-picker @emoji-click="addEmoji"></emoji-picker>
                            </div>
                            <div
                                v-if="selectedFiles.length"
                                class="px-4 pt-2 flex flex-wrap gap-2"
                            >
                                <div
                                    v-for="(file,index) in selectedFiles"
                                    :key="index"
                                    class="bg-gray-100 rounded-lg px-3 py-2 text-xs flex items-center gap-2"
                                >
                                    {{ file.name }}

                                    <button @click="selectedFiles.splice(index,1)">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </div>

                            <textarea
                                v-if="!isRecording"
                                ref="messageInput"
                                v-model="newMessageText"
                                @input="autoResize"
                                @keypress="handleKeyPress"
                                rows="1"
                                placeholder="Type a message..."
                                class="message-textarea flex-1 px-2 py-1.5 text-[15px] leading-6 bg-transparent border-none outline-none resize-none focus:outline-none focus:ring-0"
                            ></textarea>
                            
                            <!-- Record -->
                            <div
                                v-else
                                class="flex-1 flex items-center justify-between px-3"
                            >
                                <div class="flex items-center gap-3">

                                    <span class="text-red-500 animate-pulse">
                                        ●
                                    </span>

                                    <span class="text-sm font-medium">
                                        Recording...
                                    </span>

                                    <span class="text-sm text-gray-500">
                                        {{ recordingTime }}s
                                    </span>

                                </div>

                            </div>
                            <button
                                v-if="!newMessageText.trim() && !isRecording"
                                @click="startRecording"
                                class="h-9 w-9 shrink-0 flex items-center justify-center rounded-full hover:bg-grey transition shadow-sm"
                            >
                                <i class="fa-solid fa-microphone text-xl text-gray-600"></i>
                            </button>
                            <div
                                v-if="isRecording"
                                class="flex items-center gap-2"
                            >

                                <!-- Delete -->

                                <button
                                    @click="cancelRecording"
                                    class="h-9 w-9 rounded-full bg-red-500 text-white"
                                >
                                    <i class="fa-solid fa-trash"></i>
                                </button>

                                <!-- Send -->

                                <button
                                    @click="sendRecording"
                                    class="h-9 w-9 rounded-full bg-blue-500 text-white"
                                >
                                    <i class="fa-solid fa-paper-plane"></i>
                                </button>

                            </div>

                            <button
                                v-if="newMessageText.trim() && !isRecording"
                                @click="sendMessage"                                
                                class="h-9 w-9 shrink-0 flex items-center justify-center rounded-full bg-blue-500 text-white hover:bg-blue-600 transition shadow-sm"
                            >
                                <i class="fa-solid fa-paper-plane"></i>
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
                                    <div class="h-14 w-14 rounded-full bg-gradient-to-br from-blue-600 via-indigo-500 to-sky-400 flex items-center justify-center text-white font-semibold text-lg">
                                        {{ getInitials(u.contact?.name) }}
                                    </div>
                                    <span                                        
                                        class="absolute bottom-0 right-0 h-3.5 w-3.5 rounded-full bg-green-500 border-2 border-white"
                                    ></span>
                                </div>

                                <div class="flex-1 min-w-0 border-b pb-3" :class="!filteredUsers.length ? 'border-b-0' : ''">
                                    <div class="flex items-center justify-between">
                                        <div class="text-base font-semibold text-gray-900">
                                            {{ u.contact?.name }}
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div
                                                v-if="u.contact?.source === 'whatsapp'"
                                                class="h-6 w-6 rounded-full bg-green-100 flex items-center justify-center"
                                            >
                                                <i class="fa-brands fa-whatsapp text-green-600 text-sm"></i>
                                            </div>
        
                                            <div
                                                v-else-if="u.contact?.source === 'messenger'"
                                                class="h-6 w-6 rounded-full bg-blue-100 flex items-center justify-center"
                                            >
                                                <i class="fa-brands fa-facebook-messenger text-blue-600 text-sm"></i>
                                            </div>
        
                                            <div
                                                v-else
                                                class="h-6 w-6 rounded-full bg-slate-100 flex items-center justify-center"
                                            >
                                                <i class="fa-solid fa-globe text-slate-600 text-xs"></i>
                                            </div>
                                            <div class="text-xs text-gray-400">{{ timeAgo(u.last_message_at) }}</div>
                                        </div>                                        
                                    </div>
                                    <div class="text-sm text-gray-500 truncate">
                                        {{ u.latest_message?.message }}
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
                <div v-else class="w-full h-full flex flex-col" style="background-image: url('/assets/images/chatbg.png'); background-repeat: repeat; background-size: 400px; background-color: #f8f9fb;">
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
                                {{ getInitials(selectedUser.contact?.name) }}
                            </div>
                            <span                                
                                class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full bg-green-500 border-2 border-white"
                            ></span>
                        </div>
                        
                        <div class="flex-1">
                            <div class="text-sm font-semibold text-gray-900">{{ selectedUser.contact?.name }}</div>
                            <div class="text-xs" text-green-500>
                                Online
                            </div>
                        </div>
                        <button class="p-2 rounded-full hover:bg-gray-100"
                            v-if="selectedUser.contact?.platform === 'whatsapp'"                                    
                        >
                            <i class="fa-brands fa-whatsapp text-green-600"></i>
                        </button>

                        <button class="p-2 rounded-full hover:bg-gray-100"
                            v-else-if="selectedUser.contact?.platform === 'messenger'"                                
                        >
                            <i class="fa-brands fa-facebook-messenger text-blue-600"></i>
                        </button>

                        <button class="p-2 rounded-full hover:bg-gray-100"
                            v-else                                    
                        >
                            <i class="fa-solid fa-globe text-slate-600"></i>
                        </button>
                        <button class="p-2 rounded-full hover:bg-gray-100">
                            <i class="fa-solid fa-phone text-gray-600"></i>
                        </button>
                        <button class="p-2 rounded-full hover:bg-gray-100">
                            <i class="fa-solid fa-video text-gray-600"></i>
                        </button>
                    </div>

                    <!-- Messages -->
                    <div ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-3 custom-scroll">
                        <div
                            v-for="m in messages"
                            :key="m.id"
                            class="flex"
                            :class="m.sender_type === 'admin' ? 'justify-end' : 'justify-start'"
                        >
                            <div class="max-w-[85%]">
                                <div
                                    class="px-4 py-2.5 rounded-2xl text-sm"
                                    :class="m.sender_type === 'admin'
                                        ? 'bg-blue-500 text-white rounded-br-md'
                                        : 'bg-white text-gray-800 rounded-bl-md shadow-sm'"
                                >
                                    {{ m.message }}
                                </div>
                                <div
                                    v-if="m.attachments?.length"
                                    class="mt-2 space-y-2"
                                >
                                    <div
                                        v-for="file in m.attachments"
                                        :key="file.id"
                                    >

                                        <!-- Image -->
                                        <img
                                            v-if="isImage(file)"
                                            :src="`/${file.file_path}`"
                                            :alt="file.file_name"
                                            class="max-w-[250px] rounded-lg border"
                                        >

                                        <!-- Audio -->
                                        <audio
                                            v-else-if="isAudio(file)"
                                            controls
                                            class="w-full max-w-[280px]"
                                        >
                                            <source :src="`/${file.file_path}`">
                                        </audio>

                                        <!-- Video -->
                                        <video
                                            v-else-if="isVideo(file)"
                                            controls
                                            class="max-w-[300px] rounded-lg border"
                                        >
                                            <source :src="`/${file.file_path}`">
                                        </video>

                                        <!-- Other Files -->
                                        <a
                                            v-else
                                            :href="`/${file.file_path}`"
                                            target="_blank"
                                            class="flex items-center gap-2 p-2 bg-gray-100 rounded-lg hover:bg-gray-200"
                                        >
                                            <i class="fa-solid fa-file-lines text-gray-600"></i>

                                            <span class="text-xs truncate">
                                                {{ file.file_name }}
                                            </span>

                                            <i class="fa-solid fa-download ml-auto"></i>
                                        </a>

                                    </div>
                                </div>
                                <div class="text-xs text-right text-gray-400">{{ timeAgo(m.created_at) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Input -->
                    <div class="bg-white px-3 py-2 border border-gray-200 shadow-sm flex items-end gap-1.5 m-4 transition-all duration-200" :class="messageInput?.scrollHeight > 50 ? 'rounded-2xl' : 'rounded-full'">
                        <input
                            ref="fileInput"
                            type="file"
                            class="hidden"
                            multiple
                            @change="handleFiles"
                        />
                        <button
                            @click="fileInput.click()"
                            class="h-9 w-9 shrink-0 flex items-center justify-center rounded-full hover:bg-gray-100 transition text-gray-500"
                        >
                            <i class="fa-solid fa-paperclip text-lg text-gray-700"></i>
                        </button>
                        <button
                            @click="showEmojiPicker = !showEmojiPicker"
                            class="h-9 w-9 shrink-0 flex items-center justify-center rounded-full hover:bg-gray-100 transition text-gray-500"
                        >
                            <i class="fa-regular fa-face-smile text-xl text-gray-600"></i>
                        </button>
                        <div
                            v-if="selectedFiles.length"
                            class="px-4 pt-2 flex flex-wrap gap-2"
                        >
                            <div
                                v-for="(file,index) in selectedFiles"
                                :key="index"
                                class="bg-gray-100 rounded-lg px-3 py-2 text-xs flex items-center gap-2"
                            >
                                {{ file.name }}

                                <button @click="selectedFiles.splice(index,1)">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </div>

                        <div
                            v-if="showEmojiPicker"
                            class="absolute bottom-20 left-4 z-50"
                        >
                            <emoji-picker @emoji-click="addEmoji"></emoji-picker>
                        </div>
                        
                        <textarea
                            ref="messageInput"
                            v-model="newMessageText"
                            @input="autoResize"
                            @keypress="handleKeyPress"
                            rows="1"
                            placeholder="Type a message..."
                            class="message-textarea flex-1 px-2 py-1.5 text-[15px] leading-6 bg-transparent border-none outline-none resize-none focus:outline-none focus:ring-0"
                        ></textarea>
                        <button
                            v-if="!newMessageText.trim()"
                            class="h-9 w-9 shrink-0 flex items-center justify-center rounded-full hover:bg-grey transition shadow-sm"
                        >
                            <i class="fa-solid fa-microphone text-xl text-gray-600"></i>
                        </button>
                        <button
                            v-else
                            @click="sendMessage"                                
                            class="h-9 w-9 shrink-0 flex items-center justify-center rounded-full bg-blue-500 text-white hover:bg-blue-600 transition shadow-sm"
                        >
                            <i class="fa-solid fa-paper-plane"></i>
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

.message-textarea {
    overflow-x: hidden;
    scrollbar-width: thin;
}

.message-textarea::-webkit-scrollbar {
    width: 2px;
}

.message-textarea::-webkit-scrollbar-track {
    background: transparent;
}

.message-textarea::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 999px;
}

.message-textarea::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
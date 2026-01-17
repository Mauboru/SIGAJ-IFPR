<template>
    <div class="p-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">
            Bem-vindo, {{ user?.name }}!
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mb-8">Sistema de Gestão Acadêmica - IFPR</p>

        <div v-if="user?.role === 'professor'" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg hover:shadow-lg transition">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-100 dark:bg-indigo-900 rounded-md p-3">
                            <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Matérias</dt>
                                <dd class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.materias }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg hover:shadow-lg transition">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 dark:bg-green-900 rounded-md p-3">
                            <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Turmas</dt>
                                <dd class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.turmas }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg hover:shadow-lg transition">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 dark:bg-blue-900 rounded-md p-3">
                            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Aulas</dt>
                                <dd class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.aulas }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="space-y-6">
            <!-- Estatísticas de Presença -->
            <div v-if="presencaStats" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Estatísticas de Presença</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Total de Aulas</p>
                                <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ presencaStats.total_aulas }}</p>
                            </div>
                            <svg class="w-8 h-8 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                    </div>

                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-green-600 dark:text-green-400">Presenças</p>
                                <p class="text-2xl font-bold text-green-900 dark:text-green-100">{{ presencaStats.total_presencas }}</p>
                                <p class="text-xs text-green-600 dark:text-green-400 mt-1">{{ presencaStats.percentual_presenca }}%</p>
                            </div>
                            <svg class="w-8 h-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>

                    <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-red-600 dark:text-red-400">Faltas</p>
                                <p class="text-2xl font-bold text-red-900 dark:text-red-100">{{ presencaStats.total_faltas }}</p>
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ presencaStats.percentual_falta }}%</p>
                            </div>
                            <svg class="w-8 h-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                    </div>

                    <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Frequência</p>
                                <p class="text-2xl font-bold text-indigo-900 dark:text-indigo-100">{{ presencaStats.percentual_presenca }}%</p>
                            </div>
                            <svg class="w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Lista de Presenças Detalhadas -->
                <div v-if="presencaStats && presencaStats.presencas_detalhadas && presencaStats.presencas_detalhadas.length > 0" class="mt-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Detalhamento por Aula</h3>
                    </div>
                    
                    <!-- Filtros -->
                    <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Filtrar por Matéria
                            </label>
                            <select
                                v-model="filtroMateria"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 sm:text-sm px-3 py-2"
                            >
                                <option value="">Todas as matérias</option>
                                <option v-for="materia in materiasUnicas" :key="materia" :value="materia">
                                    {{ materia }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Filtrar por Data
                            </label>
                            <input
                                v-model="filtroData"
                                type="date"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 sm:text-sm px-3 py-2"
                            />
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Data</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aula</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Matéria</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Turma</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aulas do Dia</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Presenças</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Faltas</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="presenca in presencasFiltradas" :key="presenca.aula_id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ formatarData(presenca.data) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ presenca.titulo }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                        {{ presenca.materia }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                        {{ presenca.turma }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-900 dark:text-white">
                                        {{ presenca.quantidade_aulas }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center font-medium"
                                        :class="getPresencaColor(presenca)"
                                    >
                                        {{ presenca.quantidade_presencas }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center font-medium"
                                        :class="getFaltaColor(presenca)"
                                    >
                                        {{ presenca.quantidade_faltas }}
                                    </td>
                                </tr>
                                <tr v-if="presencasFiltradas.length === 0">
                                    <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                        Nenhuma presença encontrada com os filtros aplicados.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Estatísticas Resumidas -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg hover:shadow-lg transition">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-100 dark:bg-green-900 rounded-md p-3">
                                <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Turmas</dt>
                                    <dd class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.turmas }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg hover:shadow-lg transition">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-indigo-100 dark:bg-indigo-900 rounded-md p-3">
                                <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Matérias</dt>
                                    <dd class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.materias }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg hover:shadow-lg transition">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-100 dark:bg-yellow-900 rounded-md p-3">
                                <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Notas</dt>
                                    <dd class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.notas }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de Turmas -->
            <div v-if="turmas.length > 0" class="space-y-4">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Minhas Turmas</h2>
                
                <div v-for="turma in turmas" :key="turma.id" class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                    {{ turma.nome }}
                                </h3>
                                <div class="flex flex-wrap gap-4 text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    <div class="flex items-center">
                                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span>Professor: {{ turma.professor?.name || 'N/A' }}</span>
                                    </div>
                                    <div v-if="turma.ano && turma.semestre" class="flex items-center">
                                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>{{ turma.ano }}/{{ turma.semestre }}º Semestre</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <span>{{ turma.alunos?.length || 0 }} alunos</span>
                                    </div>
                                </div>

                                <!-- Alunos da Turma -->
                                <div v-if="turma.alunos && turma.alunos.length > 0" class="mb-4">
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Alunos:</h4>
                                    <div class="flex flex-wrap gap-2">
                                        <span 
                                            v-for="aluno in turma.alunos" 
                                            :key="aluno.id"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                                        >
                                            {{ aluno.name }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Matérias da Turma -->
                                <div v-if="turma.materias && turma.materias.length > 0">
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Matérias:</h4>
                                    <div class="space-y-3">
                                        <div 
                                            v-for="materia in turma.materias" 
                                            :key="materia.id"
                                            class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                        >
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <h5 class="font-semibold text-gray-900 dark:text-white mb-1">
                                                        {{ materia.nome }}
                                                    </h5>
                                                    <p v-if="materia.descricao" class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                                        {{ materia.descricao }}
                                                    </p>
                                                    <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                                                        <span v-if="materia.aulas && materia.aulas.length > 0">
                                                            {{ materia.aulas.length }} aula(s)
                                                        </span>
                                                    </div>
                                                </div>
                                                <router-link 
                                                    :to="`/aulas?materia=${materia.id}`"
                                                    class="ml-4 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-900 hover:bg-indigo-200 dark:hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 transition-colors"
                                                >
                                                    Ver Aulas
                                                </router-link>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-sm text-gray-500 dark:text-gray-400 italic">
                                    Nenhuma matéria cadastrada nesta turma
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Nenhuma turma encontrada</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Você ainda não está vinculado a nenhuma turma.</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import store from '../router/store';

const user = computed(() => store.getters.getUser());
const stats = ref({
    materias: 0,
    turmas: 0,
    aulas: 0,
    notas: 0
});

const turmas = ref([]);
const presencaStats = ref(null);
const filtroMateria = ref('');
const filtroData = ref('');

const formatarData = (data) => {
    if (!data) return '';
    const date = new Date(data);
    return date.toLocaleDateString('pt-BR');
};

// Lista única de matérias para o filtro
const materiasUnicas = computed(() => {
    if (!presencaStats.value?.presencas_detalhadas) return [];
    const materias = presencaStats.value.presencas_detalhadas.map(p => p.materia);
    return [...new Set(materias)].sort();
});

// Presenças filtradas e ordenadas
const presencasFiltradas = computed(() => {
    if (!presencaStats.value?.presencas_detalhadas) return [];
    
    let filtradas = [...presencaStats.value.presencas_detalhadas];
    
    // Filtrar por matéria
    if (filtroMateria.value) {
        filtradas = filtradas.filter(p => p.materia === filtroMateria.value);
    }
    
    // Filtrar por data
    if (filtroData.value) {
        filtradas = filtradas.filter(p => {
            const dataPresenca = new Date(p.data).toISOString().split('T')[0];
            return dataPresenca === filtroData.value;
        });
    }
    
    // Ordenar sempre da menor data para maior data
    filtradas.sort((a, b) => {
        const dataA = new Date(a.data);
        const dataB = new Date(b.data);
        return dataA - dataB;
    });
    
    return filtradas;
});

// Função para determinar cor da presença
const getPresencaColor = (presenca) => {
    const faltas = presenca.quantidade_faltas || 0;
    const total = presenca.quantidade_aulas || 1;
    
    if (faltas === 0) {
        return 'text-green-600 dark:text-green-400';
    } else if (faltas === total) {
        return 'text-red-600 dark:text-red-400';
    } else {
        return 'text-yellow-600 dark:text-yellow-400';
    }
};

// Função para determinar cor da falta
const getFaltaColor = (presenca) => {
    const faltas = presenca.quantidade_faltas || 0;
    const total = presenca.quantidade_aulas || 1;
    
    if (faltas === 0) {
        return 'text-green-600 dark:text-green-400';
    } else if (faltas === total) {
        return 'text-red-600 dark:text-red-400';
    } else {
        return 'text-yellow-600 dark:text-yellow-400';
    }
};

onMounted(async () => {
    try {
        if (user.value?.role === 'professor') {
            const [materiasRes, turmasRes, aulasRes] = await Promise.all([
                axios.get('/materias'),
                axios.get('/turmas'),
                axios.get('/aulas')
            ]);
            stats.value.materias = materiasRes.data.length;
            stats.value.turmas = turmasRes.data.length;
            stats.value.aulas = aulasRes.data.length;
        } else {
            // Carregar dados do aluno
            const [turmasRes, materiasRes, notasRes, presencaRes] = await Promise.all([
                axios.get('/turmas'),
                axios.get('/materias'),
                axios.get('/notas'),
                axios.get('/aulas/estatisticas/presenca').catch(() => null)
            ]);
            
            turmas.value = turmasRes.data || [];
            stats.value.turmas = turmasRes.data.length;
            stats.value.materias = materiasRes.data.length;
            stats.value.notas = notasRes.data.length;
            
            if (presencaRes && presencaRes.data) {
                presencaStats.value = presencaRes.data;
            }
        }
    } catch (error) {
        console.error('Erro ao carregar estatísticas:', error);
    }
});
</script>

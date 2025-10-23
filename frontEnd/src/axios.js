// src/axios.js - Configuração do Axios
import axios from 'axios';

// Configuração base do Axios
axios.defaults.baseURL = 'http://localhost:8000'; // Ajuste para a URL do seu backend
axios.defaults.headers.common['Content-Type'] = 'application/json';
axios.defaults.headers.common['Accept'] = 'application/json';

// Interceptor para requisições
axios.interceptors.request.use(
  config => {
    // Você pode adicionar tokens de autenticação aqui se necessário
    // const token = localStorage.getItem('token');
    // if (token) {
    //   config.headers.Authorization = `Bearer ${token}`;
    // }
    return config;
  },
  error => {
    return Promise.reject(error);
  }
);

// Interceptor para respostas
axios.interceptors.response.use(
  response => {
    return response;
  },
  error => {
    // Tratamento global de erros
    if (error.response) {
      // O servidor respondeu com um código de erro
      console.error('Erro na resposta:', error.response.status);

      switch (error.response.status) {
        case 401:
          console.error('Não autorizado');
          break;
        case 404:
          console.error('Recurso não encontrado');
          break;
        case 500:
          console.error('Erro interno do servidor');
          break;
        default:
          console.error('Erro:', error.response.data.message);
      }
    } else if (error.request) {
      // A requisição foi feita mas não houve resposta
      console.error('Sem resposta do servidor');
    } else {
      // Algo aconteceu ao configurar a requisição
      console.error('Erro na requisição:', error.message);
    }

    return Promise.reject(error);
  }
);

export default axios;

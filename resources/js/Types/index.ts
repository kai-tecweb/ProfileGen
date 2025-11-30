export interface Article {
  id: number;
  title: string;
  content: string;
  created_at: string;
  updated_at: string;
}

export interface Client {
  id: number;
  name: string;
  company: string | null;
  memo: string | null;
  questionnaire_text: string | null;
  answers_text: string | null;
  created_at: string;
  updated_at: string;
}

export interface Proposal {
  id: number;
  client_id: number;
  x_profile: string;
  instagram_profile: string;
  coconala_profile: string;
  product_design: string;
  created_at: string;
  updated_at: string;
  client?: Client;
}

export interface Question {
  id: number;
  question: string;
  sort_order: number;
  created_at: string;
  updated_at: string;
}

export interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at?: string | null;
}

export interface PageProps extends Record<string, any> {
  auth: {
    user: User;
  };
  articles?: {
    data: Article[];
    current_page: number;
    last_page: number;
  };
  clients?: {
    data: Client[];
    current_page: number;
    last_page: number;
  };
  article?: Article;
  client?: Client;
  proposal?: Proposal;
  proposals?: Proposal[];
  questions?: Question[];
  stats?: {
    articles_count: number;
    clients_count: number;
    proposals_count: number;
  };
  recentProposals?: Proposal[];
}


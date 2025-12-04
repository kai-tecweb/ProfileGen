import AdminV2Layout from '@/Layouts/AdminV2Layout';
import { Head, router } from '@inertiajs/react';
import { Article } from '@/Types';
import { route } from 'ziggy-js';

interface ConsultationKnowledgeIndexProps {
    articles: {
        data: Article[];
        current_page: number;
        last_page: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}

export default function Index({ articles }: ConsultationKnowledgeIndexProps) {
    return (
        <AdminV2Layout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    ナレッジ管理
                </h2>
            }
        >
            <Head title="ナレッジ管理" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            {articles.data.length > 0 ? (
                                <>
                                    <div className="overflow-x-auto">
                                        <table className="min-w-full divide-y divide-gray-200">
                                            <thead className="bg-gray-50">
                                                <tr>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        ID
                                                    </th>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        タイトル
                                                    </th>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        内容
                                                    </th>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        URL
                                                    </th>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        登録日時
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody className="bg-white divide-y divide-gray-200">
                                                {articles.data.map((article) => (
                                                    <tr key={article.id}>
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            {article.id}
                                                        </td>
                                                        <td className="px-6 py-4 text-sm text-gray-900 max-w-md">
                                                            <div className="font-medium">
                                                                {article.title.length > 50
                                                                    ? article.title.substring(0, 50) + '...'
                                                                    : article.title}
                                                            </div>
                                                        </td>
                                                        <td className="px-6 py-4 text-sm text-gray-900 max-w-md">
                                                            <div className="whitespace-pre-wrap break-words">
                                                                {article.content.length > 100
                                                                    ? article.content.substring(0, 100) + '...'
                                                                    : article.content}
                                                            </div>
                                                        </td>
                                                        <td className="px-6 py-4 text-sm text-gray-900 max-w-xs">
                                                            {article.url ? (
                                                                <a
                                                                    href={article.url}
                                                                    target="_blank"
                                                                    rel="noopener noreferrer"
                                                                    className="text-blue-600 hover:text-blue-800 underline truncate block"
                                                                    title={article.url}
                                                                >
                                                                    {article.url.length > 40
                                                                        ? article.url.substring(0, 40) + '...'
                                                                        : article.url}
                                                                </a>
                                                            ) : (
                                                                <span className="text-gray-400">-</span>
                                                            )}
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                            {new Date(article.created_at).toLocaleString('ja-JP')}
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>

                                    {/* ページネーション */}
                                    {articles.last_page > 1 && (
                                        <div className="mt-4 flex justify-center">
                                            <nav className="flex space-x-2">
                                                {articles.links.map((link, index) => (
                                                    <button
                                                        key={index}
                                                        onClick={() => {
                                                            if (link.url) {
                                                                router.visit(link.url);
                                                            }
                                                        }}
                                                        disabled={!link.url || link.active}
                                                        className={`px-3 py-2 rounded-md text-sm font-medium ${
                                                            link.active
                                                                ? 'bg-blue-600 text-white'
                                                                : link.url
                                                                ? 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
                                                                : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                                        }`}
                                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                                    />
                                                ))}
                                            </nav>
                                        </div>
                                    )}
                                </>
                            ) : (
                                <div className="text-center py-12">
                                    <p className="text-gray-500">ナレッジが登録されていません</p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AdminV2Layout>
    );
}


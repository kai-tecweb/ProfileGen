import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { PageProps } from '@/Types';
import { Question } from '@/Types';
import { route } from 'ziggy-js';
import Button from '@/Components/Button';
import { useState } from 'react';

interface QuestionsIndexProps extends PageProps {
    questions: Question[];
}

export default function Index({ questions }: QuestionsIndexProps) {
    const [copied, setCopied] = useState(false);

    const handleDelete = (id: number) => {
        if (confirm('この質問を削除してもよろしいですか？')) {
            router.delete(route('questions.destroy', id));
        }
    };

    const handleGenerate = () => {
        if (confirm('質問を生成しますか？既存の質問がある場合は、先に確認してください。')) {
            router.post(route('questions.generate'));
        }
    };

    const handleRegenerate = () => {
        if (confirm('すべての質問を削除して再生成しますか？クライアントの回答は保持されます。')) {
            router.post(route('questions.regenerate'));
        }
    };

    const handleCopyQuestions = () => {
        const text = questions
            .map((q, index) => {
                return `Q${index + 1}. ${q.question}\nA${index + 1}. \n`;
            })
            .join('\n');

        const fullText = `【ヒアリング質問】\n\n${text}\n---\n上記の質問にご回答いただき、返信をお願いいたします。`;

        navigator.clipboard.writeText(fullText).then(() => {
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex justify-between items-center">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        質問マスター管理
                    </h2>
                    <div className="space-x-2">
                        {questions.length === 0 ? (
                            <Button onClick={handleGenerate}>質問を生成</Button>
                        ) : (
                            <>
                                <Button onClick={handleRegenerate} variant="secondary">
                                    質問を再生成
                                </Button>
                                <Button onClick={handleCopyQuestions} variant="secondary">
                                    {copied ? 'コピーしました！' : '質問テキストをコピー'}
                                </Button>
                                <Link href={route('questions.create')}>
                                    <Button>新規追加</Button>
                                </Link>
                            </>
                        )}
                    </div>
                </div>
            }
        >
            <Head title="質問マスター管理" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            {questions.length > 0 ? (
                                <>
                                    <div className="mb-4">
                                        <p className="text-sm text-gray-600">
                                            質問数: {questions.length}件
                                        </p>
                                    </div>
                                    <div className="overflow-x-auto">
                                        <table className="min-w-full divide-y divide-gray-200">
                                            <thead className="bg-gray-50">
                                                <tr>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        表示順
                                                    </th>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        質問文
                                                    </th>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        操作
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody className="bg-white divide-y divide-gray-200">
                                                {questions.map((question) => (
                                                    <tr key={question.id}>
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            {question.sort_order}
                                                        </td>
                                                        <td className="px-6 py-4 text-sm text-gray-900">
                                                            {question.question}
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                            <Link
                                                                href={route('questions.edit', question.id)}
                                                                className="text-blue-600 hover:text-blue-900 mr-4"
                                                            >
                                                                編集
                                                            </Link>
                                                            <button
                                                                onClick={() => handleDelete(question.id)}
                                                                className="text-red-600 hover:text-red-900"
                                                            >
                                                                削除
                                                            </button>
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                </>
                            ) : (
                                <div className="text-center py-12">
                                    <p className="text-gray-500 mb-4">質問が登録されていません</p>
                                    <Button onClick={handleGenerate}>質問を生成</Button>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}


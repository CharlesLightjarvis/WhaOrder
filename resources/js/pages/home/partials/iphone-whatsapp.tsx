import { AnimatePresence, motion } from 'framer-motion';
import { MessageCircle } from 'lucide-react';
import { useEffect, useState } from 'react';

type ChatMessage = {
    from: 'client' | 'bot';
    text: string;
};

const defaultMessages: ChatMessage[] = [
    { from: 'client', text: 'Bonjour 👋 Vous avez le sac bleu ?' },
    { from: 'bot', text: 'Oui, en stock ✅ Sac Bleu — 15 000 FCFA' },
    { from: 'client', text: "J'en veux 2, livraison à Cocody" },
    { from: 'bot', text: 'Total : 30 000 FCFA. Mobile Money ou Cash ?' },
    { from: 'client', text: 'Mobile Money' },
    {
        from: 'bot',
        text: '🧾 Commande #482 confirmée — Livraison demain à Cocody',
    },
];

const STEP_DELAY = 1500;
const TYPING_DELAY = 900;
const START_DELAY = 700;
const RESET_PAUSE = 2800;

export function IphoneWhatsapp({
    messages = defaultMessages,
    contactName = 'Aïcha Store Assistant',
    className = '',
}: {
    messages?: ChatMessage[];
    contactName?: string;
    className?: string;
}) {
    const [visible, setVisible] = useState(0);
    const [typing, setTyping] = useState(false);

    useEffect(() => {
        let mounted = true;
        let index = 0;
        let timeoutId: ReturnType<typeof setTimeout>;

        const showNext = () => {
            if (!mounted) {
                return;
            }

            if (index >= messages.length) {
                timeoutId = setTimeout(() => {
                    if (!mounted) {
                        return;
                    }

                    index = 0;
                    setVisible(0);
                    setTyping(false);
                    timeoutId = setTimeout(showNext, START_DELAY);
                }, RESET_PAUSE);

                return;
            }

            const next = messages[index];

            if (next.from === 'bot') {
                setTyping(true);
                timeoutId = setTimeout(() => {
                    if (!mounted) {
                        return;
                    }

                    setTyping(false);
                    index += 1;
                    setVisible(index);
                    timeoutId = setTimeout(showNext, STEP_DELAY);
                }, TYPING_DELAY);
            } else {
                index += 1;
                setVisible(index);
                timeoutId = setTimeout(showNext, STEP_DELAY);
            }
        };

        timeoutId = setTimeout(showNext, START_DELAY);

        return () => {
            mounted = false;
            clearTimeout(timeoutId);
        };
    }, [messages]);

    return (
        <div className={`relative mx-auto h-[600px] w-[300px] ${className}`}>
            {/* boutons latéraux */}
            <div className="absolute top-24 -left-[3px] h-8 w-1.5 rounded-l-sm bg-neutral-800" />
            <div className="absolute top-36 -left-[3px] h-12 w-1.5 rounded-l-sm bg-neutral-800" />
            <div className="absolute top-52 -left-[3px] h-12 w-1.5 rounded-l-sm bg-neutral-800" />
            <div className="absolute top-32 -right-[3px] h-16 w-1.5 rounded-r-sm bg-neutral-800" />

            {/* châssis iPhone */}
            <div className="relative h-full w-full rounded-[3rem] border-[6px] border-neutral-900 bg-neutral-900 shadow-2xl">
                {/* dynamic island */}
                <div className="absolute top-2 left-1/2 z-20 h-7 w-28 -translate-x-1/2 rounded-full bg-neutral-900" />

                <div className="relative flex h-full w-full flex-col overflow-hidden rounded-[2.5rem] bg-[#E5DDD5]">
                    {/* en-tête WhatsApp */}
                    <div className="flex items-center gap-3 bg-[#075E54] px-4 pt-9 pb-3 text-white">
                        <div className="flex h-9 w-9 items-center justify-center rounded-full bg-white/20">
                            <MessageCircle className="h-5 w-5" />
                        </div>
                        <div>
                            <div className="text-sm font-semibold">
                                {contactName}
                            </div>
                            <div className="text-[11px] opacity-80">
                                {typing ? 'en train d’écrire…' : 'en ligne'}
                            </div>
                        </div>
                    </div>

                    {/* messages */}
                    <div className="flex flex-1 flex-col justify-end gap-2 overflow-hidden px-3 py-3">
                        <AnimatePresence initial={false}>
                            {messages
                                .slice(0, visible)
                                .map((message, index) => (
                                    <motion.div
                                        key={index}
                                        initial={{
                                            opacity: 0,
                                            y: 10,
                                            scale: 0.96,
                                        }}
                                        animate={{ opacity: 1, y: 0, scale: 1 }}
                                        transition={{ duration: 0.3 }}
                                        className={
                                            message.from === 'client'
                                                ? 'mr-auto max-w-[75%] rounded-2xl rounded-tl-sm bg-white px-3 py-2 text-[13px] text-neutral-800 shadow-sm'
                                                : 'ml-auto max-w-[75%] rounded-2xl rounded-tr-sm bg-[#DCF8C6] px-3 py-2 text-[13px] text-neutral-800 shadow-sm'
                                        }
                                    >
                                        {message.text}
                                    </motion.div>
                                ))}

                            {typing && (
                                <motion.div
                                    key="typing"
                                    initial={{ opacity: 0 }}
                                    animate={{ opacity: 1 }}
                                    exit={{ opacity: 0 }}
                                    className="ml-auto flex w-fit gap-1 rounded-2xl rounded-tr-sm bg-[#DCF8C6] px-3 py-2.5"
                                >
                                    <span className="h-1.5 w-1.5 animate-bounce rounded-full bg-neutral-500 [animation-delay:-0.3s]" />
                                    <span className="h-1.5 w-1.5 animate-bounce rounded-full bg-neutral-500 [animation-delay:-0.15s]" />
                                    <span className="h-1.5 w-1.5 animate-bounce rounded-full bg-neutral-500" />
                                </motion.div>
                            )}
                        </AnimatePresence>
                    </div>

                    {/* barre de saisie */}
                    <div className="flex items-center gap-2 bg-[#F0F0F0] px-3 py-2">
                        <div className="h-8 flex-1 rounded-full bg-white" />
                        <div className="h-8 w-8 shrink-0 rounded-full bg-[#075E54]" />
                    </div>
                </div>
            </div>
        </div>
    );
}
